

function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('overlay').classList.toggle('visible');
        }

        function togglePages(id, btn) {
            const pages = document.getElementById(id);
            pages.classList.toggle('open');
            btn.classList.toggle('open');
        }
