document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchBookInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = searchInput.value.toLowerCase();
            const table = document.getElementById('booksTable');
            const trs = table.getElementsByTagName('tr');

            for (let i = 1; i < trs.length; i++) {
                const tds = trs[i].getElementsByTagName('td');
                let found = false;
                for (let j = 0; j < tds.length; j++) {
                    if (tds[j]) {
                        if (tds[j].innerHTML.toLowerCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                trs[i].style.display = found ? "" : "none";
            }
        });
    }
});
