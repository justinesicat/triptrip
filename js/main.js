// *** SEARCH RESULT

document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const priceRange = document.getElementById('price-range');
    const priceValue = document.getElementById('price-value');
    const filtersForm = document.querySelector('.filters-section');
    const clearBtn = document.querySelector('.clear-filters');

    // Initialize slider display on page load
    priceValue.textContent = `₱ ${Number(priceRange.value).toLocaleString()}`;

    // Update slider display while dragging
    priceRange.addEventListener('input', function() {
        priceValue.textContent = `₱ ${Number(this.value).toLocaleString()}`;
    });

    // Update slider display after release (optional)
    priceRange.addEventListener('change', function() {
        priceValue.textContent = `₱ ${Number(this.value).toLocaleString()}`;
    });

    // Handle form submit
    filtersForm.addEventListener('submit', function(e) {
        e.preventDefault(); // prevent actual submit

        // Collect selected filters
        const destinations = Array.from(document.querySelectorAll('input[name="trip-destination"]:checked'))
                                  .map(el => el.value);

        const tripTypes = Array.from(document.querySelectorAll('input[name="trip-type"]:checked'))
                               .map(el => el.value);

        const location = document.querySelector('input[name="location"]:checked').value;
        const budget = priceRange.value;
        const priceSort = document.getElementById('price-sort').value;
        const reviewFilter = document.getElementById('review-filter').value;

        console.log({
            destinations,
            tripTypes,
            location,
            budget,
            priceSort,
            reviewFilter
        });

        // Redirect with query parameters
        let params = new URLSearchParams({
        'trip-destination': destinations.join(','),
        'trip-type': tripTypes.join(','),
        location,
        budget,
        'price-sort': priceSort,
        'review-filter': reviewFilter
        });
        window.location.href = `search.html?${params.toString()}`;
    });

    // Submit Button
    document.getElementById('goto-review').addEventListener('click', function () {
    });

    document.querySelector('.trip-review').addEventListener('submit', function(e) {
    const destinationInput = document.getElementById('destination').value.trim().toLowerCase();

    });
});

