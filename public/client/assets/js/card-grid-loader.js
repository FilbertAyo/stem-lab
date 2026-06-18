(function () {
    'use strict';

    const host = document.getElementById('card-grid-host');
    const skeletonTemplate = document.getElementById('card-grid-skeleton-template');

    if (!host || !skeletonTemplate) {
        return;
    }

    const indexUrl = host.dataset.indexUrl;
    const gridType = host.dataset.gridType || 'lab';
    const filterForm = document.getElementById('filterForm');
    const categoryList = document.querySelector('.category-list');
    const projectsTitle = document.getElementById('projects-grid-title');

    function isIndexNavigation(urlString) {
        try {
            const target = new URL(urlString, window.location.origin);
            const index = new URL(indexUrl, window.location.origin);

            return target.origin === index.origin && target.pathname === index.pathname;
        } catch (error) {
            return false;
        }
    }

    function showSkeleton() {
        host.innerHTML = skeletonTemplate.innerHTML;
        host.setAttribute('aria-busy', 'true');
    }

    function updateProjectsChrome(clickedLink) {
        if (gridType !== 'project' || !categoryList) {
            return;
        }

        categoryList.querySelectorAll('a').forEach(function (link) {
            link.classList.remove('active');
        });

        if (clickedLink) {
            clickedLink.classList.add('active');

            if (projectsTitle) {
                const categoryName = clickedLink.dataset.categoryName;
                projectsTitle.textContent = categoryName || 'All Projects';
            }
        }
    }

    function updateSidebarActive(clickedLink) {
        if (!categoryList || gridType === 'project') {
            return;
        }

        if (gridType === 'lab' && clickedLink && categoryList.contains(clickedLink)) {
            categoryList.querySelectorAll('a').forEach(function (link) {
                link.classList.remove('active');
            });
            clickedLink.classList.add('active');
        }
    }

    function loadGrid(url, clickedLink) {
        if (!isIndexNavigation(url)) {
            window.location.href = url;
            return;
        }

        showSkeleton();

        if (gridType === 'project') {
            updateProjectsChrome(clickedLink);
        } else {
            updateSidebarActive(clickedLink);
        }

        fetch(url, {
            headers: {
                'X-Card-Grid': '1',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html',
            },
            credentials: 'same-origin',
        })
            .then(function (response) {
                if (response.redirected) {
                    window.location.href = response.url;
                    return null;
                }

                if (!response.ok) {
                    throw new Error('Grid request failed');
                }

                return response.text();
            })
            .then(function (html) {
                if (html === null) {
                    return;
                }

                host.innerHTML = html;
                host.setAttribute('aria-busy', 'false');
                window.history.pushState({ cardGrid: true }, '', url);
            })
            .catch(function () {
                window.location.href = url;
            });
    }

    if (filterForm) {
        filterForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);
            const url = indexUrl + (params.toString() ? '?' + params.toString() : '');

            loadGrid(url);
        });
    }

    document.addEventListener('click', function (event) {
        const paginationLink = event.target.closest('#card-grid-host a.page-link, #card-grid-host .pagination a');
        if (paginationLink && paginationLink.href) {
            event.preventDefault();
            loadGrid(paginationLink.href);
            return;
        }

        const categoryLink = event.target.closest('.category-list a');
        if (categoryLink && categoryLink.href && isIndexNavigation(categoryLink.href)) {
            event.preventDefault();
            loadGrid(categoryLink.href, categoryLink);
            return;
        }

        const filterBadgeLink = event.target.closest('a.badge[href]');
        if (filterBadgeLink && filterBadgeLink.href && isIndexNavigation(filterBadgeLink.href)) {
            event.preventDefault();
            loadGrid(filterBadgeLink.href);
        }
    });

    window.addEventListener('popstate', function () {
        loadGrid(window.location.href);
    });
})();
