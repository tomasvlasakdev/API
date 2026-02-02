let currentPage;
let totalPages;

function changePage (direction) {
    let newPage = currentPage + direction;

    if(newPage < 1 || newPage > totalPages) {
        return;
    } // if condition isn't met, do nothing

    let url = new URL(window.location); // creates an object from url
    url.searchParams.set('page', newPage); // updates page
    window.location.assign(url); // navigates to new url
    if (newPage < 1) newPage = 1;
    if (newPage > totalPages) newPage = totalPages;
}

function updateFilters() {
    const level = document.getElementById('filter-level').value;
    const sort = document.getElementById('filter-sort').value;

    const baseUrl = window.location.origin + window.location.pathname;

    const newUrl = `${baseUrl}?level=${level}&sort=${sort}&page=1`;
    window.location.href = newUrl;
}