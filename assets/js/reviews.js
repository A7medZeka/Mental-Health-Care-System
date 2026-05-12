// Review System JavaScript
let selectedRating = 0;

// Initialize review functionality when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Load reviews when reviews section is shown
    const reviewsLink = document.querySelector('a[data-section="section-reviews"]');
    if (reviewsLink) {
        reviewsLink.addEventListener('click', function() {
            setTimeout(() => loadMyReviews(), 100);
        });
    }

    // Initialize rating stars
    initializeRatingStars();
});

function initializeRatingStars() {
    const stars = document.querySelectorAll('.rating-star');
    stars.forEach(star => {
        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.dataset.rating);
            updateStarDisplay(rating);
        });
        
        star.addEventListener('mouseleave', function() {
            updateStarDisplay(selectedRating);
        });
    });
}

function setRating(rating) {
    selectedRating = rating;
    updateStarDisplay(rating);
}

function updateStarDisplay(rating) {
    const stars = document.querySelectorAll('.rating-star');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.remove('btn-outline-warning');
            star.classList.add('btn-warning');
        } else {
            star.classList.remove('btn-warning');
            star.classList.add('btn-outline-warning');
        }
    });
}

async function submitReview(button) {
    const therapistId = document.getElementById('reviewTherapistId')?.value;
    const reviewText = document.getElementById('reviewText')?.value;
    const messageDiv = document.getElementById('reviewMessage');

    if (!therapistId) {
        showMessage('Therapist information not found.', 'danger', messageDiv);
        return;
    }

    if (selectedRating === 0) {
        showMessage('Please select a rating.', 'danger', messageDiv);
        return;
    }

    if (!reviewText.trim()) {
        showMessage('Please write a review.', 'danger', messageDiv);
        return;
    }

    if (reviewText.length > 1000) {
        showMessage('Review must be less than 1000 characters.', 'danger', messageDiv);
        return;
    }

    const submitButton = button || event.target;
    const originalText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';

    try {
        const response = await fetch('../../Controllers/ReviewController.php?action=review&subaction=submit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                therapist_id: parseInt(therapistId),
                rating: selectedRating,
                review_text: reviewText.trim()
            })
        });

        const result = await response.json();

        if (result.success) {
            showMessage(result.message, 'success', messageDiv);
            // Reset form
            selectedRating = 0;
            updateStarDisplay(0);
            document.getElementById('reviewText').value = '';
            // Reload reviews
            setTimeout(() => loadMyReviews(), 1000);
        } else {
            showMessage(result.message, 'danger', messageDiv);
        }
    } catch (error) {
        console.error('Error submitting review:', error);
        showMessage('Error submitting review. Please try again.', 'danger', messageDiv);
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    }
}

async function loadMyReviews() {
    const reviewsListDiv = document.getElementById('myReviewsList');
    
    if (!reviewsListDiv) return;

    reviewsListDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary-custom" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;

    try {
        const response = await fetch('../../Controllers/ReviewController.php?action=review&subaction=get_my_reviews', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        });

        const result = await response.json();

        if (result.success) {
            displayMyReviews(result.reviews);
        } else {
            reviewsListDiv.innerHTML = `
                <div class="text-center py-4">
                    <p class="text-muted">Error loading reviews.</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading reviews:', error);
        reviewsListDiv.innerHTML = `
            <div class="text-center py-4">
                <p class="text-muted">Error loading reviews. Please try again.</p>
            </div>
        `;
    }
}

function displayMyReviews(reviews) {
    const reviewsListDiv = document.getElementById('myReviewsList');
    
    if (!reviews || reviews.length === 0) {
        reviewsListDiv.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-star text-muted" style="font-size:2rem;"></i>
                <p class="text-muted mt-3">You haven't written any reviews yet.</p>
                <p class="text-muted small">Complete therapy sessions to review your therapist.</p>
            </div>
        `;
        return;
    }

    let reviewsHtml = '<div class="list-group list-group-flush">';
    
    reviews.forEach(review => {
        const stars = generateStarDisplay(review.rating);
        const statusBadge = review.moderation_status === 'Approved' 
            ? '<span class="badge bg-success">Published</span>'
            : review.moderation_status === 'Pending'
            ? '<span class="badge bg-warning text-dark">Pending Review</span>'
            : '<span class="badge bg-danger">Rejected</span>';

        reviewsHtml += `
            <div class="list-group-item p-3 border-bottom">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="fw-bold mb-1">Dr. ${review.therapist_first} ${review.therapist_last}</h6>
                        <div class="d-flex align-items-center gap-2">
                            ${stars}
                            <span class="text-muted small">${review.formatted_date}</span>
                        </div>
                    </div>
                    <div class="text-end">
                        ${statusBadge}
                    </div>
                </div>
                <p class="mb-2">${review.review_text || '<em class="text-muted">No written review provided.</em>'}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        ${review.helpful_count > 0 ? `${review.helpful_count} people found this helpful` : ''}
                    </small>
                    ${review.moderation_status === 'Approved' ? `
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="markHelpful(${review.review_id})">
                            <i class="bi bi-hand-thumbs-up me-1"></i> Helpful
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    });
    
    reviewsHtml += '</div>';
    reviewsListDiv.innerHTML = reviewsHtml;
}

function generateStarDisplay(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            stars += '<i class="bi bi-star-fill text-warning"></i>';
        } else {
            stars += '<i class="bi bi-star text-muted"></i>';
        }
    }
    return stars;
}

async function markHelpful(reviewId) {
    try {
        const response = await fetch('../../Controllers/ReviewController.php?action=review&subaction=mark_helpful', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                review_id: reviewId
            })
        });

        const result = await response.json();

        if (result.success) {
            showToast('Marked as helpful!', 'success');
            // Reload reviews to update helpful count
            setTimeout(() => loadMyReviews(), 500);
        } else {
            showToast('Error marking review as helpful', 'error');
        }
    } catch (error) {
        console.error('Error marking helpful:', error);
        showToast('Error marking review as helpful', 'error');
    }
}

function refreshMyReviews() {
    loadMyReviews();
}

function showMessage(message, type, container) {
    if (!container) return;
    
    const alertClass = type === 'success' ? 'alert-success' : 
                     type === 'danger' ? 'alert-danger' : 'alert-info';
    
    container.innerHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Auto-dismiss after 5 seconds for success messages
    if (type === 'success') {
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }
}

// Utility function to check if patient can review therapist
async function checkCanReviewTherapist(therapistId) {
    try {
        const response = await fetch(`../../Controllers/ReviewController.php?action=review&subaction=can_review&therapist_id=${therapistId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        });

        const result = await response.json();
        return result.success ? result : null;
    } catch (error) {
        console.error('Error checking review eligibility:', error);
        return null;
    }
}
