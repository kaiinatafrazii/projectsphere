/**
 * ProjectSphere - Frontend Interactions & Dynamic Logic
 */
document.addEventListener('DOMContentLoaded', function () {
    // 1. Password Visibility Toggle (Eye Icon)
    document.querySelectorAll('input[type="password"]').forEach(function (passwordInput) {
        let inputGroup = passwordInput.closest('.input-group');
        if (!inputGroup) {
            inputGroup = document.createElement('div');
            inputGroup.className = 'input-group';
            passwordInput.parentNode.insertBefore(inputGroup, passwordInput);
            inputGroup.appendChild(passwordInput);
        }

        // Check if toggle button already exists in the HTML
        let toggleButton = inputGroup.querySelector('.password-toggle, .password-toggle-btn');
        if (!toggleButton) {
            toggleButton = document.createElement('button');
            toggleButton.type = 'button';
            toggleButton.className = 'btn btn-outline-secondary password-toggle password-toggle-btn';
            toggleButton.setAttribute('aria-label', 'Show password');
            toggleButton.setAttribute('aria-pressed', 'false');
            toggleButton.innerHTML = '<i class="bi bi-eye" aria-hidden="true"></i>';
            inputGroup.appendChild(toggleButton);
        }

        toggleButton.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            toggleButton.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            toggleButton.setAttribute('aria-pressed', String(isPassword));
            
            const icon = toggleButton.querySelector('i');
            if (icon) {
                icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
            }
        });
    });

    const cookieNotice = document.getElementById('cookieNotice');
    const dismissCookieNotice = document.getElementById('dismissCookieNotice');
    if (cookieNotice && dismissCookieNotice) {
        try {
            cookieNotice.hidden = localStorage.getItem('ps_cookie_notice') === 'dismissed';
        } catch (error) {
            cookieNotice.hidden = false;
        }
        dismissCookieNotice.addEventListener('click', function () {
            try {
                localStorage.setItem('ps_cookie_notice', 'dismissed');
            } catch (error) {
                cookieNotice.hidden = true;
                return;
            }
            cookieNotice.hidden = true;
        });
    }

    // 1. Initialize Bootstrap Tooltips if available
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    const mainNavigation = document.getElementById('navbarMain');
    if (mainNavigation && typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
        mainNavigation.querySelectorAll('a[href^="#"]').forEach(function (link) {
            link.addEventListener('click', function () {
                const collapse = bootstrap.Collapse.getInstance(mainNavigation);
                if (collapse) collapse.hide();
            });
        });
    }

    // 2. Evaluation Score Live Calculator (Admin Evaluation Form)
    const evalForm = document.getElementById('evaluationForm');
    if (evalForm) {
        const inno = document.getElementById('innovation_score');
        const func = document.getElementById('functionality_score');
        const ui   = document.getElementById('ui_design_score');
        const tech = document.getElementById('tech_usage_score');
        const pres = document.getElementById('presentation_score');
        const totalDisplay = document.getElementById('totalScoreDisplay');
        const totalInput = document.getElementById('total_score');
        const progressBar = document.getElementById('scoreProgressBar');

        function calculateScore() {
            const vInno = parseFloat(inno.value) || 0;
            const vFunc = parseFloat(func.value) || 0;
            const vUi   = parseFloat(ui.value) || 0;
            const vTech = parseFloat(tech.value) || 0;
            const vPres = parseFloat(pres.value) || 0;

            const sum = Math.min(100, Math.max(0, vInno + vFunc + vUi + vTech + vPres));
            const formatted = sum.toFixed(1).replace('.0', '');

            if (totalDisplay) totalDisplay.textContent = formatted;
            if (totalInput) totalInput.value = formatted;
            if (progressBar) {
                progressBar.style.width = sum + '%';
                progressBar.textContent = formatted + '/100';
                
                // Colorize progress bar based on performance
                if (sum >= 85) {
                    progressBar.className = 'progress-bar bg-success';
                } else if (sum >= 70) {
                    progressBar.className = 'progress-bar bg-primary';
                } else if (sum >= 50) {
                    progressBar.className = 'progress-bar bg-warning text-dark';
                } else {
                    progressBar.className = 'progress-bar bg-danger';
                }
            }
        }

        [inno, func, ui, tech, pres].forEach(input => {
            if (input) {
                input.addEventListener('input', calculateScore);
            }
        });
        calculateScore(); // Initial run
    }

    // 3. Dynamic Team Member Adder (Submit / Edit Project Form)
    const addMemberBtn = document.getElementById('addTeamMemberBtn');
    const memberContainer = document.getElementById('teamMembersContainer');
    if (addMemberBtn && memberContainer) {
        addMemberBtn.addEventListener('click', function () {
            const count = memberContainer.children.length + 1;
            const row = document.createElement('div');
            row.className = 'row g-2 align-items-center mb-2 team-member-row';
            row.innerHTML = `
                <div class="col-md-5">
                    <input type="text" name="member_names[]" class="form-control" placeholder="Member Name" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="member_rolls[]" class="form-control" placeholder="Roll No (e.g. DCS-02)" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="member_roles[]" class="form-control" placeholder="Role (e.g. Frontend)">
                </div>
                <div class="col-md-1 text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-member-btn" title="Remove member">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
            memberContainer.appendChild(row);

            row.querySelector('.remove-member-btn').addEventListener('click', function () {
                row.remove();
            });
        });

        // Delegate listener for initial remove buttons
        memberContainer.addEventListener('click', function (e) {
            if (e.target.closest('.remove-member-btn')) {
                e.target.closest('.team-member-row').remove();
            }
        });
    }

    // 4. File input preview for images
    const imgInput = document.getElementById('thumbnail_image');
    const imgPreview = document.getElementById('imagePreview');
    if (imgInput && imgPreview) {
        imgInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    imgPreview.src = e.target.result;
                    imgPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
