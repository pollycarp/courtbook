// script.js
function checkAvailability() {
    const date = document.getElementById('bookingDate').value;
    const time = document.getElementById('timeSlot').value;
    const resultsDiv = document.getElementById('results');

    if (!date) {
        alert('Please select a date.');
        return;
    }

    // Show loading indicator
    resultsDiv.innerHTML = '<div class="loading">Checking availability...</div>';

    fetch(`check_availability.php?date=${date}&time=${time}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                resultsDiv.innerHTML = `<p class="error">${data.error}</p>`;
                return;
            }

            if (data.length === 0) {
                resultsDiv.innerHTML = '<p class="no-results">❌ No courts available for this time slot. Please try another time.</p>';
                return;
            }

            // Build HTML for available courts
            let html = '<h2>✅ Available Courts</h2>';
            data.forEach(court => {
                html += `
                    <div class="court-card">
                        <div class="court-info">
                            <h3>${escapeHtml(court.name)}</h3>
                            <p>${court.sport_type === 'football' ? '⚽ Football' : '🎾 Tennis'}</p>
                        </div>
                        <button class="btn-book" onclick="bookCourt(${court.id}, '${date}', '${time}')">Book Now</button>
                    </div>
                `;
            });
            resultsDiv.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            resultsDiv.innerHTML = '<p class="error">Failed to check availability. Please try again.</p>';
        });
}

function bookCourt(courtId, date, time) {
    const customerName = prompt('Enter your full name:');
    if (!customerName) return;

    const customerEmail = prompt('Enter your email (optional):', '');

    // Prepare form data
    const params = new URLSearchParams();
    params.append('court_id', courtId);
    params.append('date', date);
    params.append('time', time);
    params.append('name', customerName);
    params.append('email', customerEmail);

    fetch('book.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params
    })
    .then(response => response.text())
    .then(message => {
        alert(message);
        // Refresh availability if booking succeeded
        if (message.includes('confirmed')) {
            checkAvailability();
        }
    })
    .catch(error => {
        alert('Booking failed: ' + error);
    });
}

// Helper to prevent XSS
function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// Attach event listener when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const checkBtn = document.getElementById('checkBtn');
    if (checkBtn) {
        checkBtn.addEventListener('click', checkAvailability);
    }
    
    // Set default date to today
    const dateInput = document.getElementById('bookingDate');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.value = today;
    }
});