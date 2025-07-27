
// Environment-aware base path detection
function getBasePath() {
    const hostname = window.location.hostname;
    const isLocalhost = hostname === 'localhost' || hostname === '127.0.0.1' || hostname === '::1';
    return isLocalhost ? '/project' : '';
}

function getApiPath(endpoint) {
    return getBasePath() + '/src/backend/api/' + endpoint;
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnHTML = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

        // Gather form data
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => { data[key] = value; });

        // Client-side validation
        let valid = true;
        if (!data.name) {
            showContactError('nameError', 'Name is required'); valid = false;
        } else { showContactError('nameError', ''); }
        if (!data.email) {
            showContactError('emailError', 'Email is required'); valid = false;
        } else if (!/^\S+@\S+\.\S+$/.test(data.email)) {
            showContactError('emailError', 'Invalid email address'); valid = false;
        } else { showContactError('emailError', ''); }
        if (!data.inquiry) {
            showContactError('inquiryError', 'Please select an inquiry type'); valid = false;
        } else { showContactError('inquiryError', ''); }
        if (!data.message) {
            showContactError('messageError', 'Message is required'); valid = false;
        } else { showContactError('messageError', ''); }
        if (!valid) {
            submitBtn.innerHTML = originalBtnHTML;
            submitBtn.disabled = false;
            return;
        }

        // Send AJAX request
        fetch(getApiPath('submit_contact.php'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Sent!';
                form.reset();
                showContactMessage('success', res.message || 'Message sent successfully.');
            } else {
                submitBtn.innerHTML = originalBtnHTML;
                showContactMessage('error', res.message || 'Failed to send message.');
            }
        })
        .catch(() => {
            submitBtn.innerHTML = originalBtnHTML;
            showContactMessage('error', 'Network error. Please try again.');
        })
        .finally(() => {
            setTimeout(() => {
                submitBtn.innerHTML = originalBtnHTML;
                submitBtn.disabled = false;
            }, 1500);
        });
    });

    function showContactError(id, msg) {
        const el = document.getElementById(id);
        if (el) el.textContent = msg;
    }

    function showContactMessage(type, message) {
        let msg = document.getElementById('contact-message');
        if (!msg) {
            msg = document.createElement('div');
            msg.id = 'contact-message';
            form.parentNode.insertBefore(msg, form);
        }
        msg.className = 'contact-message ' + type;
        msg.innerHTML = message;
        msg.style.display = 'block';
        setTimeout(() => { msg.style.display = 'none'; }, 5000);
    }
});
