const API_BASE_URL = 'http://localhost:8000';

/* 🔹 Helper: fetch wrapper */
async function request(endpoint, method = "GET", body = null) {
    const options = {
        method,
        headers: { "Content-Type": "application/json" },
    };
    if (body) options.body = JSON.stringify(body);

    const response = await fetch(`${API_BASE_URL}/api${endpoint}`, options);
    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
    return response.json();
}

let posts = [];

/* 🔹 Load Posts */
async function loadPosts() {
    try {
        posts = await request("/posts");
        renderPosts();
    } catch (err) {
        console.error("Failed to load posts", err);
    }
}

/* 🔹 Create Post */
async function createPost() {
    const input = document.getElementById("postInput");
    const text = input.value.trim();

    if (!text) return;

    try {
        await request("/posts", "POST", { content: text });
        input.value = "";
        loadPosts();
    } catch (err) {
        console.error("Create post failed", err);
    }
}

/* 🔹 Add Comment */
async function addComment(postId) {
    const input = document.getElementById("c-" + postId);
    const text = input.value.trim();

    if (!text) return;

    try {
        await request("/comments", "POST", { post_id: postId, content: text });
        loadPosts();
    } catch (err) {
        console.error("Comment failed", err);
    }
}

/* 🔹 Render Posts */
function renderPosts() {
    const feed = document.getElementById("feed");
    feed.innerHTML = "";

    posts.forEach(p => {
        const el = document.createElement("div");
        el.className = "post";

        el.innerHTML = `
            <div class="post-header">
                <div class="avatar"></div>
                <strong>${p.user?.name || "User"}</strong>
                <div class="menu-container">
                    <span class="menu-btn" onclick="toggleMenu(${p.id})">&#8942;</span>
                    <div id="menu-${p.id}" class="menu">
                        <button onclick="reportPost(${p.id})">Report</button>
                    </div>
                </div>
            </div>

            <div class="post-content">${p.content || ""}</div>

            ${p.image ? `<img src="${p.image}" />` : ""}

            <div class="post-actions">
                <span>❤️ ${p.likes || 0}</span>
            </div>

            <div class="comments">
                ${(p.comments || []).map(c => `
                    <div class="comment">
                        <b>${c.user?.name || "User"}</b> ${c.content}
                    </div>
                `).join("")}
            </div>

            <div class="comment-input">
                <input id="c-${p.id}" placeholder="Add a comment..." />
                <button onclick="addComment(${p.id})">Post</button>
            </div>
        `;

        feed.appendChild(el);
    });
}

/* 🔹 Toggle post menu */
function toggleMenu(postId) {
    const menu = document.getElementById("menu-" + postId);
    menu.classList.toggle("show");
}

/* 🔹 Report Post */
async function reportPost(postId) {
    if (!confirm("Report this post?")) return;

    try {
        await request("/report", "POST", { post_id: postId });
        alert("Post reported successfully");
    } catch (err) {
        console.warn("API not ready, simulated report");
        alert("Reported (local simulation)");
    }
}

/* 🔹 Toggle sidebar pages */
function togglePages(id) {
    const pages = document.getElementById(id);
    pages.style.display = pages.style.display === "block" ? "none" : "block";
}

/* 🔹 Toggle sidebar */
function toggleSidebar() {
    const sidebar = document.querySelector(".sidebar");
    const header = document.querySelector("header");

    sidebar.classList.toggle("hide");
    header.style.marginLeft = sidebar.classList.contains("hide") ? "0" : "300px";
}

/* 🔹 Initial Load */
loadPosts();