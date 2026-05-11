const API_BASE = 'http://localhost:8000/Backend/index.php?route=/api';

document.addEventListener("DOMContentLoaded", () => {
    const proposalsList = document.getElementById("proposalsList");
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    let currentTab = 'incoming';

    if (!user.user_id) {
        window.location.href = '../auth/login.html';
        return;
    }

    async function loadProposals() {
        proposalsList.innerHTML = '<p style="text-align:center; padding:50px; color:#6b7280;">Loading proposals...</p>';
        try {
            const response = await fetch(`${API_BASE}/swap-requests&user_id=${user.user_id}&type=${currentTab}`);
            const result = await response.json();
            const proposals = result.data || [];

            if (proposals.length === 0) {
                proposalsList.innerHTML = `<div style="text-align:center; padding:50px; color:#6b7280;">
                    <i class="ti ti-arrows-exchange" style="font-size:48px; opacity:0.3;"></i>
                    <p>No ${currentTab} proposals found.</p>
                </div>`;
                return;
            }

            renderProposals(proposals);
        } catch (error) {
            proposalsList.innerHTML = '<p style="text-align:center; padding:50px; color:#ef4444;">Failed to load proposals.</p>';
        }
    }

    function renderProposals(proposals) {
        proposalsList.innerHTML = proposals.map(p => {
            const isPending = p.status === 'pending';
            const statusClass = `status-${p.status}`;
            
            return `
                <div class="proposal-card">
                    <div class="swap-deal">
                        <div class="deal-item">
                            <p style="font-size:11px; color:#64748b; margin-bottom:5px;">${currentTab === 'incoming' ? p.initiator_name : 'Me'}</p>
                            <img src="https://via.placeholder.com/80" alt="Offered">
                            <p title="${p.offered_title}">${p.offered_title || 'N/A'}</p>
                        </div>
                        
                        <div class="swap-icon">
                            <i class="ti ti-arrows-exchange"></i>
                        </div>

                        <div class="deal-item">
                            <p style="font-size:11px; color:#64748b; margin-bottom:5px;">${currentTab === 'incoming' ? 'Me' : p.partner_name}</p>
                            <img src="https://via.placeholder.com/80" alt="Requested">
                            <p title="${p.requested_title}">${p.requested_title}</p>
                        </div>
                    </div>

                    <div style="text-align: right;">
                        <div style="margin-bottom: 15px;">
                            <span class="status-badge ${statusClass}">${p.status}</span>
                        </div>
                        
                        ${currentTab === 'incoming' && isPending ? `
                            <div class="actions">
                                <button class="btn-deny" onclick="respondSwap(${p.request_id}, 'reject')">Deny</button>
                                <button class="btn-approve" onclick="respondSwap(${p.request_id}, 'accept')">Approve</button>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }).join('');
    }

    window.switchTab = (tab, el) => {
        currentTab = tab;
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
        loadProposals();
    };

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
            try {
                const response = await fetch(`${API_BASE}/swap-requests/${requestId}/${action}`, {
                    method: 'POST'
                });
                const result = await response.json();
                if (result.status === 'success') {
                    Swal.fire('Success', result.message, 'success');
                    loadProposals();
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Server connection failed', 'error');
            }
        }
    };

    loadProposals();
});
