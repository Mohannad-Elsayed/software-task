let currentTab = 'incoming';

document.addEventListener("DOMContentLoaded", () => {
    const proposalsList = document.getElementById("proposalsList");
    const pendingCount = document.getElementById("pendingCount");
    const loadingState = document.getElementById("loadingState");
    const errorState = document.getElementById("errorState");
    const emptyState = document.getElementById("emptyState");
    const contentWrapper = document.getElementById("contentWrapper");

    const incomingTab = document.getElementById("incomingTab");
    const outgoingTab = document.getElementById("outgoingTab");

    const user = JSON.parse(localStorage.getItem('user') || '{}');

    async function loadProposals() {
        if (!user.user_id) {
            window.location.href = '../auth/login.html';
            return;
        }

        try {
            loadingState.classList.remove("hidden");
            errorState.classList.add("hidden");
            emptyState.classList.add("hidden");
            contentWrapper.classList.add("hidden");

            const endpoint = `${API_BASE}/swap-requests&user_id=${user.user_id}&type=${currentTab}`;
            const response = await fetch(endpoint);
            if (!response.ok) throw new Error("Network response was not ok");

            const result = await response.json();
            const proposals = result.data || [];

            // Update pending count for incoming tab
            if (currentTab === 'incoming' && pendingCount) {
                const pending = proposals.filter(p => p.status === 'pending').length;
                pendingCount.innerHTML = pending > 0 ? `<i class="ti ti-leaf"></i> ${pending} New Offers` : '';
            } else if (pendingCount) {
                pendingCount.innerHTML = '';
            }

            if (proposals.length === 0) {
                loadingState.classList.add("hidden");
                emptyState.classList.remove("hidden");
                return;
            }

            renderProposals(proposals);
            loadingState.classList.add("hidden");
            contentWrapper.classList.remove("hidden");

        } catch (err) {
            console.error("Failed to load proposals:", err);
            loadingState.classList.add("hidden");
            errorState.classList.remove("hidden");
        }
    }

    function renderProposals(proposals) {
        proposalsList.innerHTML = "";
        proposals.forEach(p => {
            const tr = document.createElement("tr");
            const isPending = p.status === 'pending';
            const initiatorName = currentTab === 'incoming' ? (p.initiator_name || 'User') : 'Me';
            const partnerName = currentTab === 'incoming' ? 'Me' : (p.partner_name || 'User');

            tr.innerHTML = `
                <td>
                    <div style="font-weight: 600; margin-bottom: 4px;">
                        ${initiatorName} <i class="ti ti-arrows-exchange" style="color: var(--primary-green);"></i> ${partnerName}
                    </div>
                    <div style="font-size: 13px; color: #6b7280;">
                        Offering: <strong>${p.offered_title || 'N/A'}</strong>
                    </div>
                    <div style="font-size: 13px; color: #6b7280;">
                        Requested: <strong>${p.requested_title || 'N/A'}</strong>
                    </div>
                </td>
                <td>
                    <span class="status-badge status-${p.status}">${p.status}</span>
                </td>
                <td>
                    ${currentTab === 'incoming' && isPending ? `
                        <div style="display: flex; gap: 8px;">
                            <button class="btn" onclick="respondSwap(${p.request_id}, 'accept')">Accept</button>
                            <button class="btn btn-danger" onclick="respondSwap(${p.request_id}, 'reject')">Deny</button>
                        </div>
                    ` : '<span style="color:#94a3b8; font-size:12px;">No actions</span>'}
                </td>
            `;
            proposalsList.appendChild(tr);
        });
    }

    window.respondSwap = async (requestId, action) => {
        const confirm = await Swal.fire({
            title: action === 'accept' ? 'Approve Swap?' : 'Deny Swap?',
            text: action === 'accept' ? 'Ownership will be swapped immediately.' : 'This proposal will be rejected.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: action === 'accept' ? 'rgb(34, 197, 94)' : 'rgb(239, 68, 68)',
            confirmButtonText: action === 'accept' ? 'Yes, swap!' : 'Yes, deny'
        });

        if (confirm.isConfirmed) {
            try {
                const response = await fetch(`${API_BASE}/swap-requests/${requestId}/${action}`, {
                    method: 'POST'
                });
                const result = await response.json();

                if (result.status === 'success') {
                    Swal.fire('Success', result.message, 'success');
                    loadProposals();
                } else {
                    Swal.fire('Error', result.message || 'Failed to update swap request', 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Connection failed', 'error');
            }
        }
    };

    function switchTab(tab, element) {
        currentTab = tab;
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        element.classList.add('active');
        loadProposals();
    }

    if (incomingTab) incomingTab.addEventListener("click", () => switchTab('incoming', incomingTab));
    if (outgoingTab) outgoingTab.addEventListener("click", () => switchTab('outgoing', outgoingTab));

    loadProposals();
});
