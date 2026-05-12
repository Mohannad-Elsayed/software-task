/**
 * Swaps Module
 * Architecture follows the pattern established in community.js
 * Separation of concerns: UI rendering, API communication, and Event Handling
 */

let currentTab = 'incoming';

document.addEventListener("DOMContentLoaded", () => {
    const user = getLoggedUser();

    if (!user.user_id) {
        window.location.href = '../auth/login.html';
        return;
    }

    setupEventListeners();
    loadProposals();
});

/**
 * --- CORE BUSINESS LOGIC (Pure Functions) ---
 */

function getLoggedUser() {
    try {
        return JSON.parse(localStorage.getItem('user') || '{}');
    } catch (e) {
        return {};
    }
}

/**
 * Generates HTML for a single proposal card.
 * @param {Object} p - Proposal data
 * @param {string} tab - Current active tab ('incoming' or 'outgoing')
 * @returns {string} HTML string
 */
function generateProposalHTML(p, tab) {
    const isPending = p.status === 'pending';
    const statusClass = `status-${p.status}`;
    const initiatorName = tab === 'incoming' ? (p.initiator_name || 'User') : 'Me';
    const partnerName = tab === 'incoming' ? 'Me' : (p.partner_name || 'User');

    return `
        <div class="proposal-card">
            <div class="swap-deal">
                <div class="deal-item">
                    <p style="font-size:11px; color:#64748b; margin-bottom:5px;">${initiatorName}</p>
                    <p title="${p.offered_title}">${p.offered_title || 'N/A'}</p>
                </div>
                
                <div class="swap-icon">
                    <i class="ti ti-arrows-exchange"></i>
                </div>

                <div class="deal-item">
                    <p style="font-size:11px; color:#64748b; margin-bottom:5px;">${partnerName}</p>
                    <p title="${p.requested_title}">${p.requested_title}</p>
                </div>
            </div>

            <div style="text-align: right;">
                <div style="margin-bottom: 15px;">
                    <span class="status-badge ${statusClass}">${p.status}</span>
                </div>
                
                ${tab === 'incoming' && isPending ? `
                    <div class="actions">
                        <button class="btn-deny" onclick="respondSwap(${p.request_id}, 'reject')">Deny</button>
                        <button class="btn-approve" onclick="respondSwap(${p.request_id}, 'accept')">Approve</button>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
}

/**
 * --- SIDE EFFECTS (DOM Manipulation & API) ---
 */

function setupEventListeners() {
    const incomingTab = document.getElementById("incomingTab");
    const outgoingTab = document.getElementById("outgoingTab");

    if (incomingTab) {
        incomingTab.addEventListener("click", () => switchTab('incoming', incomingTab));
    }
    if (outgoingTab) {
        outgoingTab.addEventListener("click", () => switchTab('outgoing', outgoingTab));
    }
}

async function loadProposals() {
    const proposalsList = document.getElementById("proposalsList");
    const pendingCount = document.getElementById("pendingCount");
    const user = getLoggedUser();

    if (!proposalsList) return;

    proposalsList.innerHTML = '<p style="text-align:center; padding:50px; color:#6b7280;">Loading proposals...</p>';

    const endpoint = `/api/swap-requests&user_id=${user.user_id}&type=${currentTab}`;
    const result = await request(endpoint);

    if (result.status === 'success' || result.data) {
        const proposals = result.data || [];
        
        // Update pending count for incoming tab
        if (currentTab === 'incoming' && pendingCount) {
            const pending = proposals.filter(p => p.status === 'pending').length;
            pendingCount.innerHTML = pending > 0 ? `<i class="ti ti-leaf"></i> ${pending} New Offers` : '';
        } else if (pendingCount) {
            pendingCount.innerHTML = '';
        }

        renderProposals(proposals);
    } else {
        proposalsList.innerHTML = '<p style="text-align:center; padding:50px; color:#ef4444;">Failed to load proposals.</p>';
    }
}

function renderProposals(proposals) {
    const proposalsList = document.getElementById("proposalsList");
    if (!proposalsList) return;

    if (proposals.length === 0) {
        proposalsList.innerHTML = `
            <div style="text-align:center; padding:50px; color:#6b7280;">
                <i class="ti ti-arrows-exchange" style="font-size:48px; opacity:0.3;"></i>
                <p>No ${currentTab} proposals found.</p>
            </div>`;
        return;
    }

    proposalsList.innerHTML = proposals.map(p => generateProposalHTML(p, currentTab)).join('');
}

function switchTab(tab, element) {
    currentTab = tab;
    
    // Update UI
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    element.classList.add('active');
    
    // Reload data
    loadProposals();
}

/**
 * Handles responding to a swap request.
 * Exposing to window as it's called from inline HTML generated in generateProposalHTML
 * (Matches pattern in community.js where functions like submitReview are top-level)
 */
window.respondSwap = async (requestId, action) => {
    const confirm = await Swal.fire({
        title: action === 'accept' ? 'Approve Swap?' : 'Deny Swap?',
        text: action === 'accept' ? 'Ownership will be swapped immediately.' : 'This proposal will be rejected.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: action === 'accept' ? '#22c55e' : '#ef4444',
        confirmButtonText: action === 'accept' ? 'Yes, swap!' : 'Yes, deny'
    });

    if (confirm.isConfirmed) {
        const endpoint = `/api/swap-requests/${requestId}/${action}`;
        const result = await request(endpoint, 'POST');

        if (result.status === 'success') {
            Swal.fire('Success', result.message, 'success');
            loadProposals();
        } else {
            Swal.fire('Error', result.message || 'Failed to update swap request', 'error');
        }
    }
};
