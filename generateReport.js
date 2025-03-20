document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.report-trigger').forEach(item => {
        item.addEventListener('click', function () {
            fetch('stock.php')
                .then(response => response.text())
                .then(html => {
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(html, 'text/html');
                    var table = doc.getElementById('stocksTable');

                    if (!table) {
                        alert("Table not found on stock.php");
                        return;
                    }

                    var newWindow = window.open('', '', 'width=800,height=600');
                    var clonedTable = table.cloneNode(true); // Clone table

                    // Remove the last column (Action column)
                    var rows = clonedTable.getElementsByTagName('tr');
                    for (var i = 0; i < rows.length; i++) {
                        var cells = rows[i].getElementsByTagName('td');
                        if (cells.length > 0) {
                            rows[i].deleteCell(cells.length - 1); // Remove last td
                        }
                        var headers = rows[i].getElementsByTagName('th');
                        if (headers.length > 0) {
                            rows[i].deleteCell(headers.length - 1); // Remove last th
                        }
                    }

                    // Get clicked item details
                    var username = this.getAttribute('data-username');
                    var action = this.getAttribute('data-action');
                    var description = this.getAttribute('data-description');
                    var time = this.getAttribute('data-time');

                    // Adding CSS for landscape orientation and table borders
                    newWindow.document.write('<html><head><title>Report</title>');
                    newWindow.document.write('<style>');
                    newWindow.document.write('body { font-family: Arial, sans-serif; }');
                    newWindow.document.write('table { width: 100%; border-collapse: collapse; border: 1px solid #ddd; }');
                    newWindow.document.write('th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }');
                    newWindow.document.write('th { background-color: #f2f2f2; }');
                    newWindow.document.write('@media print {');
                    newWindow.document.write('@page { size: landscape; }'); // Landscape orientation
                    newWindow.document.write('body { margin: 0; }'); // Remove any default margin
                    newWindow.document.write('}'); // End print media query
                    newWindow.document.write('</style>');
                    newWindow.document.write('</head><body>');

                    newWindow.document.write('<h2>Supplier Report</h2>');
                    newWindow.document.write(`<p><strong>User:</strong> ${username}</p>`);
                    newWindow.document.write(`<p><strong>Action:</strong> ${action}</p>`);
                    newWindow.document.write(`<p><strong>Description:</strong> ${description}</p>`);
                    newWindow.document.write(`<p><strong>Time:</strong> ${time}</p>`);
                    newWindow.document.write('<hr>');
                    newWindow.document.write(clonedTable.outerHTML); // Copy the cloned table
                    newWindow.document.write('</body></html>');

                    newWindow.document.close();
                    newWindow.print(); // Trigger print dialog
                })
                .catch(error => console.error('Error fetching stock.php:', error));
        });
    });
});
