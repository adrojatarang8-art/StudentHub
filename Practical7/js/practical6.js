import { fetchJsonWithCache } from "./dataService.js";

const datasets = {
    events: {
        label: "Events",
        url: "events.json",
        cacheKey: "studentHubP6Events",
        filterField: "category",
        defaultSort: "date-asc"
    },
    students: {
        label: "Students",
        url: "students.json",
        cacheKey: "studentHubP6Students",
        filterField: "course",
        defaultSort: "name-asc"
    },
    faqs: {
        label: "FAQs",
        url: "faqs.json",
        cacheKey: "studentHubP6Faqs",
        filterField: "category",
        defaultSort: "question-asc"
    }
};

const state = {
    currentType: "events",
    data: [],
    searchText: "",
    filterValue: "all",
    sortValue: "date-asc",
    currentPage: 1,
    pageSize: 5
};

const searchInput = document.getElementById("p6Search");
const filterSelect = document.getElementById("p6Filter");
const sortSelect = document.getElementById("p6Sort");
const dataContainer = document.getElementById("p6DataContainer");
const statusBox = document.getElementById("p6Status");
const pageInfo = document.getElementById("p6PageInfo");
const prevButton = document.getElementById("p6Prev");
const nextButton = document.getElementById("p6Next");
const tabButtons = document.querySelectorAll(".p6-tab");

function setStatus(message, type = "") {
    statusBox.className = "p6-status";

    if (type) {
        statusBox.classList.add(type);
    }

    statusBox.textContent = message;
}

function escapeHtml(value) {
    return String(value)
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

async function loadDataset(type) {
    state.currentType = type;
    state.currentPage = 1;
    state.searchText = "";
    state.filterValue = "all";

    searchInput.value = "";

    const config = datasets[type];
    state.sortValue = config.defaultSort;

    setStatus(`Loading ${config.label.toLowerCase()}...`, "loading");
    dataContainer.innerHTML = "";

    try {
        const result = await fetchJsonWithCache(config.url, config.cacheKey);
        state.data = result.data;

        updateActiveTab();
        buildFilterOptions();
        buildSortOptions();
        renderView();

        if (result.source === "cache") {
            setStatus(result.message, "cache");
        } else {
            setStatus(result.message);
        }

    } catch (error) {
        state.data = [];
        dataContainer.innerHTML = `
            <div class="p6-empty">
                <strong>Data could not be loaded.</strong><br>
                Start the project with VS Code Live Server and try again.
            </div>
        `;
        pageInfo.textContent = "Page 0 of 0";
        prevButton.disabled = true;
        nextButton.disabled = true;
        setStatus(`Error: ${error.message}`, "error");
        console.error("Practical 6 fetch error:", error);
    }
}

function updateActiveTab() {
    tabButtons.forEach(button => {
        const active = button.dataset.dataset === state.currentType;
        button.classList.toggle("active", active);
        button.setAttribute("aria-pressed", String(active));
    });
}

function buildFilterOptions() {
    const field = datasets[state.currentType].filterField;

    const values = [...new Set(
        state.data
            .map(item => item[field])
            .filter(Boolean)
    )].sort((a, b) => String(a).localeCompare(String(b)));

    filterSelect.innerHTML = '<option value="all">All</option>';

    values.forEach(value => {
        const option = document.createElement("option");
        option.value = value;
        option.textContent = value;
        filterSelect.appendChild(option);
    });

    filterSelect.value = "all";
}

function buildSortOptions() {
    const options = {
        events: [
            ["date-asc", "Date: Oldest First"],
            ["date-desc", "Date: Newest First"],
            ["title-asc", "Title: A to Z"],
            ["title-desc", "Title: Z to A"]
        ],
        students: [
            ["name-asc", "Name: A to Z"],
            ["name-desc", "Name: Z to A"],
            ["enrollment-asc", "Enrollment: Ascending"],
            ["enrollment-desc", "Enrollment: Descending"]
        ],
        faqs: [
            ["question-asc", "Question: A to Z"],
            ["question-desc", "Question: Z to A"],
            ["category-asc", "Category: A to Z"],
            ["category-desc", "Category: Z to A"]
        ]
    };

    sortSelect.innerHTML = "";

    options[state.currentType].forEach(([value, label]) => {
        const option = document.createElement("option");
        option.value = value;
        option.textContent = label;
        sortSelect.appendChild(option);
    });

    sortSelect.value = state.sortValue;
}

function getProcessedData() {
    const config = datasets[state.currentType];
    const query = state.searchText.trim().toLowerCase();

    // filter() is used for both search and category/course filtering.
    let result = state.data.filter(item => {
        const matchesSearch = !query || Object.values(item)
            .some(value => String(value).toLowerCase().includes(query));

        const matchesFilter = state.filterValue === "all" ||
            String(item[config.filterField]) === state.filterValue;

        return matchesSearch && matchesFilter;
    });

    // Copy the array before sort() so original fetched data is not mutated.
    result = [...result].sort(getSortFunction(state.sortValue));

    return result;
}

function getSortFunction(sortValue) {
    const [field, direction] = sortValue.split("-");
    const multiplier = direction === "desc" ? -1 : 1;

    return (a, b) => {
        if (field === "date") {
            return (new Date(a.date) - new Date(b.date)) * multiplier;
        }

        return String(a[field] ?? "")
            .localeCompare(String(b[field] ?? ""), undefined, { numeric: true }) * multiplier;
    };
}

function renderView() {
    const processed = getProcessedData();
    const totalPages = Math.max(1, Math.ceil(processed.length / state.pageSize));

    if (state.currentPage > totalPages) {
        state.currentPage = totalPages;
    }

    const start = (state.currentPage - 1) * state.pageSize;

    // slice() performs pagination.
    const pageData = processed.slice(start, start + state.pageSize);

    if (processed.length === 0) {
        dataContainer.innerHTML = `
            <div class="p6-empty">
                No matching ${datasets[state.currentType].label.toLowerCase()} found.
            </div>
        `;
    } else {
        // map() creates reusable HTML cards from JSON records.
        dataContainer.innerHTML = pageData.map(renderCard).join("");
    }

    const displayPage = processed.length === 0 ? 0 : state.currentPage;
    const displayTotal = processed.length === 0 ? 0 : totalPages;

    pageInfo.textContent = `Page ${displayPage} of ${displayTotal} • ${processed.length} result(s)`;
    prevButton.disabled = state.currentPage <= 1 || processed.length === 0;
    nextButton.disabled = state.currentPage >= totalPages || processed.length === 0;
}

function renderCard(item) {
    if (state.currentType === "events") {
        const formattedDate = new Date(item.date + "T00:00:00")
            .toLocaleDateString("en-IN", {
                day: "2-digit",
                month: "short",
                year: "numeric"
            });

        return `
            <article class="p6-card">
                <h3>${escapeHtml(item.title)}</h3>
                <p><strong>Date:</strong> ${escapeHtml(formattedDate)}</p>
                <p><strong>Venue:</strong> ${escapeHtml(item.venue)}</p>
                <p>${escapeHtml(item.description)}</p>
                <span class="p6-badge">${escapeHtml(item.category)}</span>
            </article>
        `;
    }

    if (state.currentType === "students") {
        return `
            <article class="p6-card">
                <h3>${escapeHtml(item.name)}</h3>
                <p><strong>Enrollment:</strong> ${escapeHtml(item.enrollment)}</p>
                <p><strong>Course:</strong> ${escapeHtml(item.course)}</p>
                <p><strong>Semester:</strong> ${escapeHtml(item.semester)}</p>
                <p><strong>City:</strong> ${escapeHtml(item.city)}</p>
                <span class="p6-badge">${escapeHtml(item.skill)}</span>
            </article>
        `;
    }

    return `
        <article class="p6-card">
            <h3>${escapeHtml(item.question)}</h3>
            <p>${escapeHtml(item.answer)}</p>
            <span class="p6-badge">${escapeHtml(item.category)}</span>
        </article>
    `;
}

function setupEvents() {
    tabButtons.forEach(button => {
        button.addEventListener("click", () => {
            loadDataset(button.dataset.dataset);
        });
    });

    searchInput.addEventListener("input", event => {
        state.searchText = event.target.value;
        state.currentPage = 1;
        renderView();
    });

    filterSelect.addEventListener("change", event => {
        state.filterValue = event.target.value;
        state.currentPage = 1;
        renderView();
    });

    sortSelect.addEventListener("change", event => {
        state.sortValue = event.target.value;
        state.currentPage = 1;
        renderView();
    });

    prevButton.addEventListener("click", () => {
        if (state.currentPage > 1) {
            state.currentPage--;
            renderView();
        }
    });

    nextButton.addEventListener("click", () => {
        const total = getProcessedData().length;
        const totalPages = Math.ceil(total / state.pageSize);

        if (state.currentPage < totalPages) {
            state.currentPage++;
            renderView();
        }
    });
}

// Intermediate extension: dependent Country -> State -> City dropdowns.
const locationData = {
    India: {
        Gujarat: ["Ahmedabad", "Anand", "Rajkot", "Surat", "Vadodara"],
        Maharashtra: ["Mumbai", "Nagpur", "Pune"]
    },
    USA: {
        California: ["Los Angeles", "San Diego", "San Francisco"],
        Texas: ["Austin", "Dallas", "Houston"]
    },
    Canada: {
        Ontario: ["Ottawa", "Toronto"],
        Alberta: ["Calgary", "Edmonton"]
    }
};

function setupDependentDropdowns() {
    const country = document.getElementById("countrySelect");
    const stateSelect = document.getElementById("stateSelect");
    const city = document.getElementById("citySelect");

    Object.keys(locationData).forEach(countryName => {
        country.add(new Option(countryName, countryName));
    });

    country.addEventListener("change", () => {
        stateSelect.innerHTML = '<option value="">-- Select State --</option>';
        city.innerHTML = '<option value="">-- Select City --</option>';
        stateSelect.disabled = !country.value;
        city.disabled = true;

        if (!country.value) return;

        Object.keys(locationData[country.value]).forEach(stateName => {
            stateSelect.add(new Option(stateName, stateName));
        });
    });

    stateSelect.addEventListener("change", () => {
        city.innerHTML = '<option value="">-- Select City --</option>';
        city.disabled = !stateSelect.value;

        if (!stateSelect.value) return;

        locationData[country.value][stateSelect.value].forEach(cityName => {
            city.add(new Option(cityName, cityName));
        });
    });
}

setupEvents();
setupDependentDropdowns();
loadDataset("events");

// Practical 4 already adds a static FAQ section globally.
// On this page, Practical 6 has a JSON-driven FAQ view, so remove the duplicate.
window.addEventListener("DOMContentLoaded", () => {
    const oldFaqSection = document.getElementById("faqSection");
    if (oldFaqSection) {
        oldFaqSection.remove();
    }
});

console.log("StudentHub Practical 6 module loaded successfully!");
