// Select all buttons and add event listeners

const prevBtn = document.getElementById('previous');
const nextBtn = document.getElementById('next');
const searchTextBox = document.getElementById('search-text');
const sortByInput = document.getElementById('sort-by');
const sortOrderInput = document.getElementById('sort-order');
const lastPageBtn = document.getElementById('end_page');

let search_text = '';
let sort_by = 'fullname';
let sort_order = 'ASC';
let cost_min = 0.00;
let cost_max = 10.00;


// Event listener for previous button
prevBtn.addEventListener('click', function () {
    fetchPageData(this.getAttribute('data-page'));
});

// Event listener for next button
nextBtn.addEventListener('click', function () {
    fetchPageData(this.getAttribute('data-page'));
});

// Event listener for First Page button
document.getElementById('start_page').addEventListener('click', function () {
    fetchPageData(this.getAttribute('data-page'));
});

// Event listener for Last Page button
document.getElementById('end_page').addEventListener('click', function () {
    fetchPageData(this.getAttribute('data-page'));
});

// Fetch data from API on changing sort by field
sortByInput.addEventListener('change', function () {
    sort_by = this.value;
    fetchPageData();
});

// Fetch data from API on changing sort order field
sortOrderInput.addEventListener('change', function () {
    sort_order = this.value;
    fetchPageData();
});

// Initial pagination event listeners initialization
setPaginationEventListeners();

// Load page function
function loadPage(page_data) {
    const table_body = document.getElementById('table-body');
    table_body.innerHTML = '';
    page_data.forEach((row) => {
        const tr = document.createElement('tr');
        Object.keys(row).forEach((key, idx) => {
            if (idx > 0) {
                let elem;
                if (idx === 1) {
                    elem = document.createElement('th');
                    elem.scope = 'row';
                }
                else {
                    elem = document.createElement('td');
                }
                elem.innerHTML = row[key];
                tr.appendChild(elem);
            }
        });
        const elem = document.createElement('td');
        elem.classList.add('text-center');
        elem.innerHTML= `<button role="button" class="btn btn-primary" title="Send EMail" data-email="${row.email}" data-fullname="${row.fullname}" data-bs-target="#emailModal" data-bs-toggle="modal"><i class="bi bi-envelope-at"></i></button>`;
        tr.appendChild(elem);
        table_body.appendChild(tr);
    });
}

// Setting pagination event function
function setPaginationEventListeners() {
    const pages = document.getElementsByClassName('page-number');
    Array.from(pages).forEach((page) => {
        page.addEventListener('click', function () {
            fetchPageData(this.getAttribute('data-page'));
        });
    });
}

// Updating pagination function
function updatePagination(page_number, max_page_number) {
    if (page_number > max_page_number) {
        return;
    }
    if (page_number < 1) {
        return;
    }
    let pages_array = [], min_page = 1, max_page = max_page_number;
    min_page = Math.max(page_number - 2, 1);
    max_page = Math.min(min_page + 4, max_page_number);

    if (page_number >= max_page_number - 1) {
        min_page = Math.max(page_number - 4, 1);
    }

    for (let i = min_page; i <= max_page; i++) {
        pages_array.push(i);
    }

    const pagination = document.getElementById('pagination');
    const prevPagination = document.getElementsByClassName('page-number');
    Array.from(prevPagination).forEach((page) => {
        pagination.removeChild(page.parentElement);
    });

    pages_array.forEach((page) => {
        const pagination_item = document.createElement('li');
        pagination_item.classList.add('page-item');
        const pagination_link = document.createElement('button');
        pagination_link.classList.add('page-link');
        pagination_link.classList.add('page-number');
        if (page == page_number) {
            pagination_link.classList.add('active');
        }
        pagination_link.textContent = page;
        pagination_link.setAttribute('data-page', page);
        pagination_item.appendChild(pagination_link);
        pagination.insertBefore(pagination_item, pagination.lastElementChild.previousElementSibling);
    });

    lastPageBtn.setAttribute('data-page', max_page_number);

    if (page_number == 1) {
        prevBtn.classList.add('disabled');
    }
    else {
        prevBtn.classList.remove('disabled');
    }
    if (page_number == max_page_number) {
        nextBtn.classList.add('disabled');
    }
    else {
        nextBtn.classList.remove('disabled');
    }
    prevBtn.setAttribute('data-page', parseInt(page_number) - 1);
    nextBtn.setAttribute('data-page', parseInt(page_number) + 1);

    setPaginationEventListeners();
}

// Data fetching function based on page number
function fetchPageData(page_number = 1) {
    const url = 'chargePointAPI.php';
    const params = {
        page: page_number,
        search_term: search_text,
        cost_min: parseFloat(cost_min),
        cost_max: parseFloat(cost_max),
        order_by: sort_by,
        order_type: sort_order
    };

    const xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-type', 'application/json;charset=UTF-8');
    xhr.onreadystatechange = () => {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.status_code === 200) {
                    loadPage(response.data);
                    updatePagination(page_number, response.max_pages);
                }
                else {
                    alert(response.message);
                }
            }
        }
    };
    xhr.send(JSON.stringify(params));
}

// Double slider for cost filter
const slider = document.getElementById('cost-slider');
noUiSlider.create(slider, {
    start: [0, 10],
    connect: true,
    range: {
        'min': [0],
        'max': [10],
    },
    'margin': 0.05,
    'step': 0.01,
    'tooltips': true,
});

// Search when search button is clicked
document.getElementById("button-search-text").addEventListener("click", function () {
    search_text = searchTextBox.value;
    cost_min = slider.noUiSlider.get()[0];
    cost_max = slider.noUiSlider.get()[1];
    fetchPageData();
});

// Email modal setting
const modal = document.getElementById('emailModal');
if (modal) {
    modal.addEventListener('show.bs.modal', event => {
        const btn = event.relatedTarget;
        const fullname = btn.getAttribute('data-fullname');
        const email = btn.getAttribute('data-email');

        const modal_heading = modal.querySelector('#cp-owner-name');
        modal_heading.textContent = fullname;

        const hidden_email = modal.querySelector('#owner-email');
        hidden_email.value = email;
    });
}