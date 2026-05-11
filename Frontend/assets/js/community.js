let selectedRating = 0;

document.addEventListener("DOMContentLoaded", function () {
    setupStars();
    loadReviews();

    const postBtn = document.getElementById("postBtn");
    if (postBtn) {
        postBtn.addEventListener("click", submitReview);
    }
});

function getLoggedUserId() {
    return localStorage.getItem("user_id");
}

function setupStars() {
    document.querySelectorAll("#stars span").forEach(star => {
        star.addEventListener("click", function () {
            selectedRating = Number(this.dataset.v);

            document.querySelectorAll("#stars span").forEach(s => {
                s.style.color =
                    Number(s.dataset.v) <= selectedRating
                        ? "#f5c518"
                        : "#ddd";
            });
        });
    });
}

async function loadReviews() {
    let endpoint = "/api/community/reviews";

    const result = await request(endpoint);

    const reviews = result.reviews || [];
    renderReviews(reviews);
}

function renderReviews(reviews) {
    const feed = document.getElementById("feed");

    if (!reviews.length) {
        feed.innerHTML = `<p class="empty">No reviews yet.</p>`;
        return;
    }

    feed.innerHTML = reviews.map(review => `
        <div class="review-card">
            <div class="top">
                <span class="name">${review.username || "User"}</span>
                <span class="date">Review #${review.review_id}</span>
            </div>

            <div class="stars-display">
                ${"★".repeat(review.rating)}
                ${"☆".repeat(5 - review.rating)}
            </div>

            <p>${review.comment || ""}</p>
        </div>
    `).join("");
}

async function submitReview() {
    const userId = getLoggedUserId();
    const text = document.getElementById("reviewText").value.trim();

    if (!userId) {
        alert("Please login first");
        return;
    }

    if (!selectedRating || !text) {
        alert("Please choose a rating and write a review");
        return;
    }

    const result = await request(
        "/api/community/reviews",
        "POST",
        {
            user_id: Number(userId),
            rating: Number(selectedRating),
            comment: text
        }
    );

    if (result.success) {
        alert("Review posted successfully");

        document.getElementById("reviewText").value = "";
        selectedRating = 0;

        document.querySelectorAll("#stars span").forEach(s => {
            s.style.color = "#ddd";
        });

        loadReviews();
    } else {
        alert(result.message || "Failed to post review");
    }
}