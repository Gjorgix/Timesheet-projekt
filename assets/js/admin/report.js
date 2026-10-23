
import $ from 'jquery';


$("#generate-raport").on("submit", function (event) {
    event.preventDefault();

    $.ajax({
        url: path_app_admin_report_ajax,
        method: "POST",
        data: $("#generate-raport").serialize(),
        success: function (response) {
            console.log(response);
            console.log(response["selected_employee"]["firstName"])
            renderReport(response)
        },
        error: function () {
            console.error("Błąd przy tworzeniu tabeli");
        }
    });
});

function renderReport(response){
    const container = $('#report-container');
    container.empty();

    if (!response.selected_employee) {
        container.append(
            $('<p>').text('Nie znaleziono pracownika.')
        );
        return;
    }

    const employee = response.selected_employee;
    container.append(
        $('<h2>').text(
            'Wpisy: ' + employee["firstName"] + ' ' +
            employee["lastName"]
        )
    );

    const timeEntries = response.time_entries || [];
    if (timeEntries.length === 0) {
        container.append(
            $("<h3>").text("Brak wpisów.")
        );
    } else {
        const entryTable = createTable(["Start","Koniec","Projekt","Czas twania", "Komentarz"])

        const entryBody= $("<tbody>")
        timeEntries.forEach((entry)=> {
            const row = $('<tr>');

            row.append(createCell(entry.start_at,"td"));
            row.append(createCell(entry.end_at,"td"));
            row.append(createCell(entry.project,"td"));
            row.append(createCell(formatDuration(entry.duration),"td"));
            row.append(createCell(entry.comment,"td"));


            entryBody.append(row);
        });


        entryTable.append(entryBody)
        container.append(entryTable)

        container.append(
            $('<h2>').text('Podsumowanie czasu według projektu')
        );

        const totalsTable = createTable(['Projekt','Łączny czas']);
        const totalsBody = totalsTable.find('tbody');

        const totals = response.project_totals;
        console.log(totals)

        let totalMinutes = 0;

        totals.forEach(function (item) {
            const minutes = Number(item.minutes) || 0;
            totalMinutes += minutes;

            const row = $('<tr>');

            row.append(createCell(item.project, 'td'));
            row.append(createCell(formatDuration(minutes), 'td'));

            totalsBody.append(row);
        });


        const totalRow = $('<tr>');

        totalRow.append(createCell('Razem', 'th'));
        totalRow.append(
            createCell(formatDuration(totalMinutes), 'th')
        );

        totalsBody.append(totalRow);
        container.append(totalsTable);
    }

    if (timeEntries.length === 0) {
        const totalsTable = createTable(['Projekt', 'Łączny czas']);
        const totalsBody = totalsTable.find('tbody');
        totalsBody.append(
            $('<tr>').append(createCell('Razem', 'th'), createCell('00:00', 'th'))
        );
        container.append(
            $('<h2>').text('Podsumowanie czasu według projektu'),
            totalsTable
        );
    }
}
function formatDuration(minutes){
    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;

    return String(hours).padStart(2, '0') + ':' +
        String(remainingMinutes).padStart(2, '0');
}

function createTable(headers) {
    const $table = $('<table>');
    const $thead = $('<thead>');
    const $headerRow = $('<tr>');

    headers.forEach(function (header) {
        $headerRow.append(createCell(header, 'th'));
    });

    $thead.append($headerRow);
    $table.append($thead);
    $table.append($('<tbody>'));

    return $table;
}
function createCell(text, tag){
    return $("<" + tag + ">").text(text)
}
