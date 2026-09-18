document.addEventListener("DOMContentLoaded", function () {

    const nav = document.querySelector("nav");
    const header = document.querySelector("header");

    // ===============================
    // Practical 4 - Hamburger Menu
    // ===============================

    if (nav && header) {

        const menuButton = document.createElement("button");

        menuButton.id = "menuToggle";
        menuButton.type = "button";
        menuButton.textContent = "☰ Menu";

        menuButton.setAttribute(
            "aria-expanded",
            "false"
        );

        menuButton.setAttribute(
            "aria-controls",
            "mainNav"
        );

        nav.id = "mainNav";

        header.insertBefore(
            menuButton,
            nav
        );


        menuButton.addEventListener(
            "click",
            function () {

                nav.classList.toggle(
                    "nav-open"
                );

                const isOpen =
                    nav.classList.contains(
                        "nav-open"
                    );

                menuButton.setAttribute(
                    "aria-expanded",
                    String(isOpen)
                );
            }
        );
    }


    // ===============================
    // Practical 4 - Theme Switcher
    // ===============================

    const themeButton =
        document.createElement("button");

    themeButton.id = "themeToggle";
    themeButton.type = "button";

    const savedTheme =
        localStorage.getItem(
            "studentHubTheme"
        );


    if (savedTheme === "dark") {

        document.body.classList.add(
            "dark-theme"
        );

        themeButton.textContent =
            "☀️ Light Mode";

    } else {

        themeButton.textContent =
            "🌙 Dark Mode";
    }


    themeButton.addEventListener(
        "click",
        function () {

            document.body.classList.toggle(
                "dark-theme"
            );

            const darkMode =
                document.body.classList.contains(
                    "dark-theme"
                );

            localStorage.setItem(
                "studentHubTheme",
                darkMode
                    ? "dark"
                    : "light"
            );

            themeButton.textContent =
                darkMode
                    ? "☀️ Light Mode"
                    : "🌙 Dark Mode";
        }
    );


    if (header) {

        header.appendChild(
            themeButton
        );
    }


    // ===============================
    // Practical 4 - Notification Banner
    // ===============================

    const notification =
        document.createElement("div");

    notification.id =
        "notificationBanner";

    notification.setAttribute(
        "role",
        "status"
    );

    notification.innerHTML = `
        <span>
            Welcome to StudentHub Portal!
        </span>

        <button
            type="button"
            id="closeNotification"
            aria-label="Close notification">
            ×
        </button>
    `;

    document.body.prepend(
        notification
    );


    const closeNotification =
        document.getElementById(
            "closeNotification"
        );


    closeNotification.addEventListener(
        "click",
        function () {

            notification.remove();

        }
    );


    // ===============================
    // Practical 4 - Modal Popup
    // ===============================

    const modal =
        document.createElement("div");

    modal.id = "studentHubModal";

    modal.setAttribute(
        "role",
        "dialog"
    );

    modal.setAttribute(
        "aria-modal",
        "true"
    );

    modal.setAttribute(
        "aria-labelledby",
        "modalTitle"
    );


    modal.innerHTML = `

        <div class="modal-box">

            <button
                type="button"
                id="closeModal"
                aria-label="Close popup">
                ×
            </button>

            <h2 id="modalTitle">
                StudentHub
            </h2>

            <p>
                Welcome to StudentHub Portal.
                Manage your academic activities
                easily in one place.
            </p>

            <button
                type="button"
                id="modalOk">
                OK
            </button>

        </div>
    `;


    document.body.appendChild(
        modal
    );


    function openModal() {

        modal.classList.add(
            "show-modal"
        );

        document.getElementById(
            "closeModal"
        ).focus();
    }


    function closeModal() {

        modal.classList.remove(
            "show-modal"
        );
    }


    document.getElementById(
        "closeModal"
    ).addEventListener(
        "click",
        closeModal
    );


    document.getElementById(
        "modalOk"
    ).addEventListener(
        "click",
        closeModal
    );


    modal.addEventListener(
        "click",
        function (event) {

            if (event.target === modal) {

                closeModal();
            }
        }
    );


    // ===============================
    // Practical 4 - FAQ
    // ===============================

    const faqSection =
        document.createElement(
            "section"
        );

    faqSection.id =
        "faqSection";


    faqSection.innerHTML = `

        <h2>
            Frequently Asked Questions
        </h2>

        <details>

            <summary>
                What is StudentHub?
            </summary>

            <p>
                StudentHub is an online
                portal for managing courses,
                attendance, events and
                student information.
            </p>

        </details>


        <details>

            <summary>
                Can I switch between
                light and dark mode?
            </summary>

            <p>
                Yes. Use the theme button.
                Your choice is saved in
                localStorage.
            </p>

        </details>


        <details>

            <summary>
                How can I contact
                the administrator?
            </summary>

            <p>
                Open the Contact page
                and submit the contact form.
            </p>

        </details>
    `;


    const mainForFaq =
        document.querySelector(
            "main"
        );


    if (
        mainForFaq &&
        !document.getElementById(
            "faqSection"
        )
    ) {

        mainForFaq.appendChild(
            faqSection
        );
    }


    const faqItems =
        document.querySelectorAll(
            "#faqSection details"
        );


    faqItems.forEach(
        function (item) {

            item.addEventListener(
                "toggle",
                function () {

                    if (item.open) {

                        faqItems.forEach(
                            function (
                                otherItem
                            ) {

                                if (
                                    otherItem
                                    !== item
                                ) {

                                    otherItem.open =
                                        false;
                                }
                            }
                        );
                    }
                }
            );
        }
    );


    // ==========================================
    // Normal Forms Validation
    // ==========================================

    const forms =
        document.querySelectorAll(
            "form"
        );


    forms.forEach(
        function (form) {

            // Registration form has
            // separate validation below

            if (
                form.id ===
                "registrationForm"
            ) {
                return;
            }


            form.addEventListener(
                "submit",
                function (event) {

                    const requiredFields =
                        form.querySelectorAll(
                            "[required]"
                        );

                    let valid = true;


                    requiredFields.forEach(
                        function (field) {

                            const empty =
                                field.type ===
                                "checkbox"
                                    ? !field.checked
                                    : field.value.trim()
                                        === "";


                            if (empty) {

                                valid = false;

                                field.style.border =
                                    "2px solid red";

                                field.setAttribute(
                                    "aria-invalid",
                                    "true"
                                );

                            } else {

                                field.style.border =
                                    "";

                                field.removeAttribute(
                                    "aria-invalid"
                                );
                            }
                        }
                    );


                    event.preventDefault();


                    if (!valid) {

                        alert(
                            "Please fill all required fields."
                        );

                    } else {

                        alert(
                            "Form submitted successfully!"
                        );

                        form.reset();
                    }
                }
            );
        }
    );


    // ==================================================
    // Practical 5
    // Registration Form Frontend Validation
    // ==================================================

    const registrationForm =
        document.getElementById(
            "registrationForm"
        );


    if (registrationForm) {

        // Get Inputs

        const regName =
            document.getElementById(
                "regName"
            );

        const regEmail =
            document.getElementById(
                "regEmail"
            );

        const regMobile =
            document.getElementById(
                "regMobile"
            );

        const regPassword =
            document.getElementById(
                "regPassword"
            );

        const confirmPassword =
            document.getElementById(
                "confirmPassword"
            );

        const regCourse =
            document.getElementById(
                "regCourse"
            );

        const regYear =
            document.getElementById(
                "regYear"
            );

        const terms =
            document.getElementById(
                "terms"
            );

        const successMessage =
            document.getElementById(
                "registrationSuccess"
            );

        const strengthBar =
            document.getElementById(
                "strengthBar"
            );

        const strengthText =
            document.getElementById(
                "strengthText"
            );


        // ===============================
        // Regular Expressions
        // ===============================

        const nameRegex =
            /^[A-Za-z]+(?:[ '-][A-Za-z]+)*$/;


        const emailRegex =
            /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;


        const mobileRegex =
            /^[6-9][0-9]{9}$/;


        const passwordRegex =
            /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#^])[A-Za-z\d@$!%*?&#^]{8,}$/;


        // ===============================
        // Show Error
        // ===============================

        function showFieldError(
            field,
            errorId,
            message
        ) {

            document.getElementById(
                errorId
            ).textContent =
                message;


            field.classList.add(
                "invalid-field"
            );


            field.classList.remove(
                "valid-field"
            );


            field.setAttribute(
                "aria-invalid",
                "true"
            );
        }


        // ===============================
        // Show Valid
        // ===============================

        function showFieldValid(
            field,
            errorId
        ) {

            document.getElementById(
                errorId
            ).textContent =
                "";


            field.classList.remove(
                "invalid-field"
            );


            field.classList.add(
                "valid-field"
            );


            field.setAttribute(
                "aria-invalid",
                "false"
            );
        }


        // ===============================
        // Name Validation
        // ===============================

        function validateName() {

            const value =
                regName.value.trim();


            if (value === "") {

                showFieldError(
                    regName,
                    "nameError",
                    "Full name is required."
                );

                return false;
            }


            if (
                value.length < 3 ||
                !nameRegex.test(value)
            ) {

                showFieldError(
                    regName,
                    "nameError",
                    "Enter at least 3 letters. Only letters, spaces, apostrophe and hyphen are allowed."
                );

                return false;
            }


            showFieldValid(
                regName,
                "nameError"
            );

            return true;
        }


        // ===============================
        // Email Validation
        // ===============================

        function validateEmail() {

            const value =
                regEmail.value.trim();


            if (value === "") {

                showFieldError(
                    regEmail,
                    "emailError",
                    "Email is required."
                );

                return false;
            }


            if (
                !emailRegex.test(
                    value
                )
            ) {

                showFieldError(
                    regEmail,
                    "emailError",
                    "Enter a valid email address."
                );

                return false;
            }


            showFieldValid(
                regEmail,
                "emailError"
            );

            return true;
        }


        // ===============================
        // Mobile Validation
        // ===============================

        function validateMobile() {

            const value =
                regMobile.value.trim();


            if (value === "") {

                showFieldError(
                    regMobile,
                    "mobileError",
                    "Mobile number is required."
                );

                return false;
            }


            if (
                !mobileRegex.test(
                    value
                )
            ) {

                showFieldError(
                    regMobile,
                    "mobileError",
                    "Enter valid 10-digit mobile number starting with 6, 7, 8 or 9."
                );

                return false;
            }


            showFieldValid(
                regMobile,
                "mobileError"
            );

            return true;
        }


        // ===============================
        // Password Strength
        // ===============================

        function updatePasswordStrength() {

            const value =
                regPassword.value;

            let score = 0;


            if (value.length >= 8)
                score++;


            if (
                /[a-z]/.test(value)
            )
                score++;


            if (
                /[A-Z]/.test(value)
            )
                score++;


            if (
                /\d/.test(value)
            )
                score++;


            if (
                /[@$!%*?&#^]/.test(
                    value
                )
            )
                score++;


            if (
                value.length === 0
            ) {

                strengthBar.style.width =
                    "0%";

                strengthText.textContent =
                    "";

                return;
            }


            const width =
                score * 20;


            strengthBar.style.width =
                width + "%";


            if (score <= 2) {

                strengthBar.style.background =
                    "#b00020";

                strengthText.textContent =
                    "Password strength: Weak";

            } else if (
                score <= 4
            ) {

                strengthBar.style.background =
                    "#d97706";

                strengthText.textContent =
                    "Password strength: Medium";

            } else {

                strengthBar.style.background =
                    "#198754";

                strengthText.textContent =
                    "Password strength: Strong";
            }
        }


        // ===============================
        // Password Validation
        // ===============================

        function validatePassword() {

            const value =
                regPassword.value;


            updatePasswordStrength();


            if (value === "") {

                showFieldError(
                    regPassword,
                    "passwordError",
                    "Password is required."
                );

                return false;
            }


            if (
                !passwordRegex.test(
                    value
                )
            ) {

                showFieldError(
                    regPassword,
                    "passwordError",
                    "Password must contain uppercase, lowercase, number and special character."
                );

                return false;
            }


            showFieldValid(
                regPassword,
                "passwordError"
            );

            return true;
        }


        // ===============================
        // Confirm Password
        // ===============================

        function validateConfirmPassword() {

            if (
                confirmPassword.value
                === ""
            ) {

                showFieldError(
                    confirmPassword,
                    "confirmPasswordError",
                    "Please confirm your password."
                );

                return false;
            }


            if (
                confirmPassword.value
                !== regPassword.value
            ) {

                showFieldError(
                    confirmPassword,
                    "confirmPasswordError",
                    "Passwords do not match."
                );

                return false;
            }


            showFieldValid(
                confirmPassword,
                "confirmPasswordError"
            );

            return true;
        }


        // ===============================
        // Course Validation
        // ===============================

        function validateCourse() {

            if (
                regCourse.value === ""
            ) {

                showFieldError(
                    regCourse,
                    "courseError",
                    "Please select a course."
                );

                return false;
            }


            showFieldValid(
                regCourse,
                "courseError"
            );

            return true;
        }


        // ===============================
        // Year Validation
        // ===============================

        function validateYear() {

            if (
                regYear.value === ""
            ) {

                showFieldError(
                    regYear,
                    "yearError",
                    "Please select your year."
                );

                return false;
            }


            showFieldValid(
                regYear,
                "yearError"
            );

            return true;
        }


        // ===============================
        // Gender Validation
        // ===============================

        function validateGender() {

            const selectedGender =
                registrationForm
                    .querySelector(
                        'input[name="gender"]:checked'
                    );


            const genderError =
                document.getElementById(
                    "genderError"
                );


            const genderFields =
                registrationForm
                    .querySelectorAll(
                        'input[name="gender"]'
                    );


            if (!selectedGender) {

                genderError.textContent =
                    "Please select a gender.";


                genderFields.forEach(
                    function (field) {

                        field.setAttribute(
                            "aria-invalid",
                            "true"
                        );
                    }
                );


                return false;
            }


            genderError.textContent =
                "";


            genderFields.forEach(
                function (field) {

                    field.setAttribute(
                        "aria-invalid",
                        "false"
                    );
                }
            );


            return true;
        }


        // ===============================
        // Terms Validation
        // ===============================

        function validateTerms() {

            if (!terms.checked) {

                document.getElementById(
                    "termsError"
                ).textContent =
                    "You must accept the Terms and Conditions.";


                terms.setAttribute(
                    "aria-invalid",
                    "true"
                );


                return false;
            }


            document.getElementById(
                "termsError"
            ).textContent =
                "";


            terms.setAttribute(
                "aria-invalid",
                "false"
            );


            return true;
        }


        // =====================================
        // Real-Time Validation
        // Intermediate Extension
        // =====================================

        regName.addEventListener(
            "keyup",
            validateName
        );


        regEmail.addEventListener(
            "keyup",
            validateEmail
        );


        regMobile.addEventListener(
            "keyup",
            validateMobile
        );


        regPassword.addEventListener(
            "keyup",
            function () {

                validatePassword();


                if (
                    confirmPassword.value
                    !== ""
                ) {

                    validateConfirmPassword();
                }
            }
        );


        confirmPassword.addEventListener(
            "keyup",
            validateConfirmPassword
        );


        regCourse.addEventListener(
            "change",
            validateCourse
        );


        regYear.addEventListener(
            "change",
            validateYear
        );


        registrationForm
            .querySelectorAll(
                'input[name="gender"]'
            )
            .forEach(
                function (radio) {

                    radio.addEventListener(
                        "change",
                        validateGender
                    );
                }
            );


        terms.addEventListener(
            "change",
            validateTerms
        );


        // ===============================
        // Submit Validation
        // ===============================

        registrationForm.addEventListener(
            "submit",
            function (event) {

                event.preventDefault();


                successMessage.textContent =
                    "";


                const results = [

                    validateName(),

                    validateEmail(),

                    validateMobile(),

                    validatePassword(),

                    validateConfirmPassword(),

                    validateCourse(),

                    validateYear(),

                    validateGender(),

                    validateTerms()
                ];


                const isValid =
                    results.every(
                        function (result) {

                            return result === true;
                        }
                    );


                if (isValid) {

                    successMessage.textContent =
                        "Registration successful! All entered details are valid.";

                } else {

                    const firstInvalid =
                        registrationForm
                            .querySelector(
                                '[aria-invalid="true"]'
                            );


                    if (firstInvalid) {

                        firstInvalid.focus();
                    }
                }
            }
        );


        // ===============================
        // Reset Form
        // ===============================

        registrationForm.addEventListener(
            "reset",
            function () {

                setTimeout(
                    function () {

                        registrationForm
                            .querySelectorAll(
                                ".error-message"
                            )
                            .forEach(
                                function (
                                    error
                                ) {

                                    error.textContent =
                                        "";
                                }
                            );


                        registrationForm
                            .querySelectorAll(
                                ".invalid-field, .valid-field"
                            )
                            .forEach(
                                function (
                                    field
                                ) {

                                    field.classList.remove(
                                        "invalid-field",
                                        "valid-field"
                                    );

                                    field.removeAttribute(
                                        "aria-invalid"
                                    );
                                }
                            );


                        strengthBar.style.width =
                            "0%";


                        strengthText.textContent =
                            "";


                        successMessage.textContent =
                            "";

                    },
                    0
                );
            }
        );
    }


    // ===============================
    // Show / Hide Password
    // ===============================

    const passwordFields =
        document.querySelectorAll(
            'input[type="password"]'
        );


    passwordFields.forEach(
        function (password) {

            const button =
                document.createElement(
                    "button"
                );


            button.type =
                "button";

            button.textContent =
                "Show";

            button.className =
                "password-toggle";


            button.setAttribute(
                "aria-label",
                "Show or hide password"
            );


            password.insertAdjacentElement(
                "afterend",
                button
            );


            button.addEventListener(
                "click",
                function () {

                    if (
                        password.type ===
                        "password"
                    ) {

                        password.type =
                            "text";

                        button.textContent =
                            "Hide";

                    } else {

                        password.type =
                            "password";

                        button.textContent =
                            "Show";
                    }
                }
            );
        }
    );


    // ===============================
    // Table Hover
    // ===============================

    const tableRows =
        document.querySelectorAll(
            "table tr"
        );


    tableRows.forEach(
        function (row, index) {

            if (index === 0)
                return;


            row.addEventListener(
                "mouseenter",
                function () {

                    row.classList.add(
                        "active-row"
                    );
                }
            );


            row.addEventListener(
                "mouseleave",
                function () {

                    row.classList.remove(
                        "active-row"
                    );
                }
            );
        }
    );


    // ===============================
    // Active Navigation
    // ===============================

    const currentPage =
        window.location.pathname
            .split("/")
            .pop()
        || "index.html";


    const navLinks =
        document.querySelectorAll(
            "nav a"
        );


    navLinks.forEach(
        function (link) {

            if (
                link.getAttribute(
                    "href"
                )
                === currentPage
            ) {

                link.classList.add(
                    "active-nav"
                );


                link.setAttribute(
                    "aria-current",
                    "page"
                );
            }
        }
    );


    // ===============================
    // Escape Key Close Modal
    // ===============================

    document.addEventListener(
        "keydown",
        function (event) {

            if (
                event.key ===
                "Escape"
            ) {

                closeModal();
            }
        }
    );


    // ===============================
    // Practical 4 Dynamic CSS
    // ===============================

    const style =
        document.createElement(
            "style"
        );


    style.textContent = `

        #menuToggle,
        #themeToggle,
        #welcomeButton,
        .slider-controls button,
        #modalOk,
        .password-toggle {

            padding: 8px 14px;
            margin: 5px;
            cursor: pointer;
            border-radius: 6px;
            border: 1px solid #888;
            background: #f4f4f4;
        }


        #themeToggle {

            float: right;
        }


        #mainNav.nav-open {

            display: flex;
            flex-wrap: wrap;
        }


        #notificationBanner {

            padding: 10px 15px;
            margin: 0;
            background: #fff3cd;
            border: 1px solid #ffe69c;

            display: flex;

            justify-content:
                space-between;

            align-items:
                center;
        }


        #closeNotification,
        #closeModal {

            border: 0;
            background: transparent;
            font-size: 24px;
            cursor: pointer;
        }


        #studentHubModal {

            display: none;

            position: fixed;

            inset: 0;

            background:
                rgba(0,0,0,.55);

            z-index: 1000;

            align-items: center;

            justify-content: center;
        }


        #studentHubModal.show-modal {

            display: flex;
        }


        .modal-box {

            background: white;

            color: #222;

            width:
                min(90%, 450px);

            padding: 25px;

            border-radius:
                12px;

            position: relative;

            box-shadow:
                0 10px 30px
                rgba(0,0,0,.3);
        }


        #closeModal {

            position: absolute;

            right: 10px;

            top: 5px;
        }


        #faqSection details {

            margin: 10px 0;

            padding: 12px;

            border:
                1px solid #ccc;

            border-radius:
                8px;
        }


        #faqSection summary {

            cursor: pointer;

            font-weight: bold;
        }


        .active-row {

            outline:
                2px solid #888;

            transform:
                scale(1.01);

            transition:
                .2s;
        }


        .active-nav {

            font-weight: bold;

            text-decoration:
                underline;
        }


        body.dark-theme {

            background:
                #121212;

            color:
                #f1f1f1;
        }


        body.dark-theme header,
        body.dark-theme footer,
        body.dark-theme section,
        body.dark-theme article,
        body.dark-theme aside {

            background:
                #1e1e1e;

            color:
                #f1f1f1;
        }


        body.dark-theme a {

            color:
                #8ab4f8;
        }


        body.dark-theme table {

            color:
                #f1f1f1;
        }


        body.dark-theme input,
        body.dark-theme textarea,
        body.dark-theme select {

            background:
                #2b2b2b;

            color:
                #fff;
        }


        @media
        (max-width: 700px) {

            #mainNav {

                display: none;

                flex-direction:
                    column;
            }


            #mainNav.nav-open {

                display: flex;
            }


            #themeToggle {

                float: none;

                display: block;

                margin:
                    10px auto;
            }
        }
    `;


    document.head.appendChild(
        style
    );


    console.log(
        "StudentHub Practical 5 JavaScript loaded successfully!"
    );

});