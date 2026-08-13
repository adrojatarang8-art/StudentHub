
document.addEventListener("DOMContentLoaded", function () {

    // ==========================================
    // 1. HAMBURGER MENU
    // ==========================================

    const nav = document.querySelector("nav");
    const header = document.querySelector("header");

    if (nav && header) {

        const menuButton = document.createElement("button");

        menuButton.id = "menuToggle";
        menuButton.type = "button";
        menuButton.textContent = "☰ Menu";
        menuButton.setAttribute("aria-expanded", "false");

        header.insertBefore(menuButton, nav);

        menuButton.addEventListener("click", function () {

            nav.classList.toggle("nav-open");

            const isOpen = nav.classList.contains("nav-open");

            menuButton.setAttribute(
                "aria-expanded",
                isOpen
            );

        });
    }


    // 2. LIGHT / DARK THEME

    const themeButton = document.createElement("button");

    themeButton.id = "themeToggle";
    themeButton.type = "button";

    const savedTheme =
        localStorage.getItem("studentHubTheme");

    if (savedTheme === "dark") {

        document.body.classList.add("dark-theme");

        themeButton.textContent =
            "☀️ Light Mode";

    } else {

        themeButton.textContent =
            "🌙 Dark Mode";
    }

    themeButton.addEventListener("click", function () {

        document.body.classList.toggle("dark-theme");

        const darkMode =
            document.body.classList.contains("dark-theme");

        localStorage.setItem(
            "studentHubTheme",
            darkMode ? "dark" : "light"
        );

        themeButton.textContent =
            darkMode
                ? "☀️ Light Mode"
                : "🌙 Dark Mode";

    });

    if (header) {
        header.appendChild(themeButton);
    }


    // 3. NOTIFICATION BANNER

    const notification =
        document.createElement("div");

    notification.id =
        "notificationBanner";

    notification.innerHTML = `
        <span>
            Welcome to StudentHub Portal!
        </span>

        <button type="button"
                id="closeNotification">
            ×
        </button>
    `;

    document.body.prepend(notification);


    const closeNotification =
        document.getElementById(
            "closeNotification"
        );

    if (closeNotification) {

        closeNotification.addEventListener(
            "click",
            function () {

                notification.style.display =
                    "none";

            }
        );
    }


    
    // 4. MODAL POPUP


    const modal =
        document.createElement("div");

    modal.id = "studentHubModal";

    modal.innerHTML = `
        <div class="modal-box">

            <button type="button"
                    id="closeModal">
                ×
            </button>

            <h2>StudentHub</h2>

            <p>
                Welcome to StudentHub Portal.
                Manage your academic activities
                easily in one place.
            </p>

        </div>
    `;

    document.body.appendChild(modal);


    function openModal() {

        modal.classList.add("show-modal");

    }


    function closeModal() {

        modal.classList.remove(
            "show-modal"
        );

    }


    const closeModalButton =
        document.getElementById(
            "closeModal"
        );

    if (closeModalButton) {

        closeModalButton.addEventListener(
            "click",
            closeModal
        );

    }


    modal.addEventListener(
        "click",
        function (event) {

            if (event.target === modal) {
                closeModal();
            }

        }
    );

    // 5. COLLAPSIBLE FAQ

    const faqItems =
        document.querySelectorAll(
            "details"
        );

    faqItems.forEach(function (item) {

        item.addEventListener(
            "toggle",
            function () {

                if (item.open) {

                    faqItems.forEach(
                        function (otherItem) {

                            if (
                                otherItem !== item
                            ) {

                                otherItem.open =
                                    false;

                            }

                        }
                    );

                }

            }
        );

    });

    // 6. FORM VALIDATION

    const forms =
        document.querySelectorAll("form");

    forms.forEach(function (form) {

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

                        if (
                            field.value.trim() === ""
                        ) {

                            valid = false;

                            field.style.border =
                                "2px solid red";

                        } else {

                            field.style.border =
                                "";

                        }

                    }
                );

                if (!valid) {

                    event.preventDefault();

                    alert(
                        "Please fill all required fields."
                    );

                }

            }
        );

    });

    // 7. SHOW / HIDE PASSWORD

    const passwordFields =
        document.querySelectorAll(
            'input[type="password"]'
        );

    passwordFields.forEach(
        function (password) {

            const button =
                document.createElement("button");

            button.type = "button";
            button.textContent = "Show";
            button.className =
                "password-toggle";

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


    // 8. TABLE ROW HOVER / DOM MANIPULATION

    const tableRows =
        document.querySelectorAll(
            "table tbody tr"
        );

    tableRows.forEach(function (row) {

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

    });

    // 9. ACTIVE NAVIGATION

    const currentPage =
        window.location.pathname
            .split("/")
            .pop() || "index.html";

    const navLinks =
        document.querySelectorAll(
            "nav a"
        );

    navLinks.forEach(function (link) {

        if (
            link.getAttribute("href") ===
            currentPage
        ) {

            link.classList.add(
                "active-nav"
            );

            link.setAttribute(
                "aria-current",
                "page"
            );

        }

    });

    // 10. KEYBOARD ESCAPE FOR MODAL

    document.addEventListener(
        "keydown",
        function (event) {

            if (event.key === "Escape") {

                closeModal();

            }

        }
    );


    console.log(
        "StudentHub Practical 4 JavaScript loaded successfully!"
    );

});