    document.addEventListener("DOMContentLoaded", function () {

    const nav = document.querySelector("nav");
    const header = document.querySelector("header");

    if (nav && header) {
        const menuButton = document.createElement("button");
        menuButton.id = "menuToggle";
        menuButton.type = "button";
        menuButton.textContent = "☰ Menu";
        menuButton.setAttribute("aria-expanded", "false");
        menuButton.setAttribute("aria-controls", "mainNav");

        nav.id = "mainNav";
        header.insertBefore(menuButton, nav);

        menuButton.addEventListener("click", function () {
            nav.classList.toggle("nav-open");
            const isOpen = nav.classList.contains("nav-open");
            menuButton.setAttribute("aria-expanded", String(isOpen));
        });
    }

    const themeButton = document.createElement("button");
    themeButton.id = "themeToggle";
    themeButton.type = "button";

    const savedTheme = localStorage.getItem("studentHubTheme");

    if (savedTheme === "dark") {
        document.body.classList.add("dark-theme");
        themeButton.textContent = "☀️ Light Mode";
    } else {
        themeButton.textContent = "🌙 Dark Mode";
    }

    themeButton.addEventListener("click", function () {
        document.body.classList.toggle("dark-theme");
        const darkMode = document.body.classList.contains("dark-theme");

        localStorage.setItem("studentHubTheme", darkMode ? "dark" : "light");
        themeButton.textContent = darkMode ? "☀️ Light Mode" : "🌙 Dark Mode";
    });

    if (header) {
        header.appendChild(themeButton);
    }

    const notification = document.createElement("div");
    notification.id = "notificationBanner";
    notification.setAttribute("role", "status");
    notification.innerHTML = `
        <span>Welcome to StudentHub Portal!</span>
        <button type="button" id="closeNotification" aria-label="Close notification">×</button>
    `;

    document.body.prepend(notification);

    const closeNotification = document.getElementById("closeNotification");
    closeNotification.addEventListener("click", function () {
        notification.remove();
    });

    const modal = document.createElement("div");
    modal.id = "studentHubModal";
    modal.setAttribute("role", "dialog");
    modal.setAttribute("aria-modal", "true");
    modal.setAttribute("aria-labelledby", "modalTitle");
    modal.innerHTML = `
        <div class="modal-box">
            <button type="button" id="closeModal" aria-label="Close popup">×</button>
            <h2 id="modalTitle">StudentHub</h2>
            <p>Welcome to StudentHub Portal. Manage your academic activities easily in one place.</p>
            <button type="button" id="modalOk">OK</button>
        </div>
    `;
    document.body.appendChild(modal);

    function openModal() {
        modal.classList.add("show-modal");
        document.getElementById("closeModal").focus();
    }

    function closeModal() {
        modal.classList.remove("show-modal");
    }

    document.getElementById("closeModal").addEventListener("click", closeModal);
    document.getElementById("modalOk").addEventListener("click", closeModal);

    modal.addEventListener("click", function (event) {
        if (event.target === modal) closeModal();
    });

    const faqSection = document.createElement("section");
    faqSection.id = "faqSection";
    faqSection.innerHTML = `
        <h2>Frequently Asked Questions</h2>
        <details>
            <summary>What is StudentHub?</summary>
            <p>StudentHub is an online portal for managing courses, attendance, events and student information.</p>
        </details>
        <details>
            <summary>Can I switch between light and dark mode?</summary>
            <p>Yes. Use the theme button. Your choice is saved in localStorage.</p>
        </details>
        <details>
            <summary>How can I contact the administrator?</summary>
            <p>Open the Contact page and submit the contact form.</p>
        </details>
    `;

    const mainForFaq = document.querySelector("main");
    if (mainForFaq && !document.getElementById("faqSection")) {
        mainForFaq.appendChild(faqSection);
    }

    const faqItems = document.querySelectorAll("#faqSection details");
    faqItems.forEach(function (item) {
        item.addEventListener("toggle", function () {
            if (item.open) {
                faqItems.forEach(function (otherItem) {
                    if (otherItem !== item) otherItem.open = false;
                });
            }
        });
    });
  
    const forms = document.querySelectorAll("form");

    forms.forEach(function (form) {
        form.addEventListener("submit", function (event) {
            const requiredFields = form.querySelectorAll("[required]");
            let valid = true;

            requiredFields.forEach(function (field) {
                if (field.value.trim() === "") {
                    valid = false;
                    field.style.border = "2px solid red";
                    field.setAttribute("aria-invalid", "true");
                } else {
                    field.style.border = "";
                    field.removeAttribute("aria-invalid");
                }
            });

            if (!valid) {
                event.preventDefault();
                alert("Please fill all required fields.");
            } else {
                // Keep the demo on the same page instead of submitting to a server.
                event.preventDefault();
                alert("Form submitted successfully!");
                form.reset();
            }
        });
    });

    const passwordFields = document.querySelectorAll('input[type="password"]');

    passwordFields.forEach(function (password) {
        const button = document.createElement("button");
        button.type = "button";
        button.textContent = "Show";
        button.className = "password-toggle";
        button.setAttribute("aria-label", "Show or hide password");

        password.insertAdjacentElement("afterend", button);

        button.addEventListener("click", function () {
            if (password.type === "password") {
                password.type = "text";
                button.textContent = "Hide";
            } else {
                password.type = "password";
                button.textContent = "Show";
            }
        });
    });

    const tableRows = document.querySelectorAll("table tr");

    tableRows.forEach(function (row, index) {
        if (index === 0) return; // skip heading row

        row.addEventListener("mouseenter", function () {
            row.classList.add("active-row");
        });

        row.addEventListener("mouseleave", function () {
            row.classList.remove("active-row");
        });
    });


    const currentPage =
        window.location.pathname.split("/").pop() || "index.html";

    const navLinks = document.querySelectorAll("nav a");

    navLinks.forEach(function (link) {
        if (link.getAttribute("href") === currentPage) {
            link.classList.add("active-nav");
            link.setAttribute("aria-current", "page");
        }
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeModal();
        }
    });

    const style = document.createElement("style");
    style.textContent = `
        #menuToggle, #themeToggle, #welcomeButton,
        .slider-controls button, #modalOk, .password-toggle {
            padding: 8px 14px;
            margin: 5px;
            cursor: pointer;
            border-radius: 6px;
            border: 1px solid #888;
            background: #f4f4f4;
        }

        #themeToggle { float: right; }

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
            justify-content: space-between;
            align-items: center;
        }

        #closeNotification, #closeModal {
            border: 0;
            background: transparent;
            font-size: 24px;
            cursor: pointer;
        }

        #studentHubModal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.55);
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
            width: min(90%, 450px);
            padding: 25px;
            border-radius: 12px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,.3);
        }

        #closeModal {
            position: absolute;
            right: 10px;
            top: 5px;
        }

        #faqSection details {
            margin: 10px 0;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        #faqSection summary {
            cursor: pointer;
            font-weight: bold;
        }

        #contentSlider {
            margin-top: 25px;
            text-align: center;
        }

        .slide {
            display: none;
            padding: 30px;
            border: 1px solid #ccc;
            border-radius: 10px;
            min-height: 90px;
            animation: fadeSlide .35s ease-in-out;
        }

        .active-slide {
            display: block;
        }

        @keyframes fadeSlide {
            from { opacity: .3; transform: translateX(15px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .active-row {
            outline: 2px solid #888;
            transform: scale(1.01);
            transition: .2s;
        }

        .active-nav {
            font-weight: bold;
            text-decoration: underline;
        }

        body.dark-theme {
            background: #121212;
            color: #f1f1f1;
        }

        body.dark-theme header,
        body.dark-theme footer,
        body.dark-theme section,
        body.dark-theme article,
        body.dark-theme aside {
            background: #1e1e1e;
            color: #f1f1f1;
        }

        body.dark-theme a {
            color: #8ab4f8;
        }

        body.dark-theme table {
            color: #f1f1f1;
        }

        body.dark-theme input,
        body.dark-theme textarea,
        body.dark-theme select {
            background: #2b2b2b;
            color: #fff;
        }

        @media (max-width: 700px) {
            #mainNav {
                display: none;
                flex-direction: column;
            }

            #mainNav.nav-open {
                display: flex;
            }

            #themeToggle {
                float: none;
                display: block;
                margin: 10px auto;
            }
        }
    `;
    document.head.appendChild(style);

    console.log("StudentHub Practical 4 JavaScript loaded successfully!");
});
