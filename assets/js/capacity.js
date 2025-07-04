$(document).ready(function() {
    capacity();
});

function capacity() {

    let capacity = 180000;

    $.getJSON('../src/controllers/capacity.ajax.php?opc=1', (res) => {
        let events = [];

        res.data.forEach(function(item) {

            let Thits = item.hits;
            let date = item.fecha;
            let available = capacity - Thits;

            events.push({
                title: "",
                start: date,
                extendedProps: {
                    totalHits: Thits,
                    available: available,
                    capacity: capacity,
                    date: date
                }
            });
        });

        let calendarEl = $('#calendar')[0];
        let calendar = new FullCalendar.Calendar(calendarEl, {
            initialDate: '2025-06-23',
            initialView: 'dayGridMonth',
            events: events,
            eventContent: function(arg) {

                let { date, totalHits, available, capacity } = arg.event.extendedProps;
                let dateFormat = new Date(date).toLocaleDateString('en-us', {timeZone:"UTC", year:"numeric", month:"short", day:"numeric"});
                let disp = new Intl.NumberFormat().format(available);
                let totalH = new Intl.NumberFormat().format(totalHits);
                let progress = (totalHits / capacity) * 100;
                let color = progress > 80 ? '#dc3545' : '#0b6efd';

                let container = document.createElement('div');
                container.innerHTML = `
                    <div class="progress-container cursor-pointer">
                        <div class="progress-bar" style="width: ${progress}%; background-color: ${color};"></div>
                    </div>
                    <div class="Flex">
                        <span class="text-dark">${disp}</span><span class="text-dark"> hits</span><span class="text-dark hide"> available</span>
                    </div>`;
                    
                $(container).find('.progress-container').click(function() {
                    $('#total').val(totalH);
                    $('#modalDate1, #modalDate2').val(dateFormat);
                    dataByDate(date);
                    $('#title').html(`<h4 class="modal-title fw-bold" id="exampleModalLabel">${dateFormat}</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>`);
                    $('#infoCapacity').modal('show');
                });
                return { domNodes: [container]};
            }
        });
        calendar.render();
    });
}

function dataByDate(fecha) {

    $.ajax({
        url: '../src/controllers/capacity.ajax.php?opc=2',
        method: 'GET',
        data: {date:fecha},
        dataType: 'json',
        success: function(res) {
            
            let hits = res.hits;
            let style = res.style;
            let styleIn = res.styleIncomp;
            let hitsIn = res.hitsIncomp;
            let hitsIncom = new Intl.NumberFormat().format(hitsIn);
            let hitsC = new Intl.NumberFormat().format(hits);

            $('#modalDate').val(fecha);
            $('#complete').html(`<p class="mb-0">Styles: ${style}</p><p>Hits: ${hitsC}</p>`);
            $('#incomplete').html(`<p class="mb-0">Styles: ${styleIn}</p><p>Hits: ${hitsIncom}</p>`);
        },
        error: function() {
            alert("error");
        }
    });
}

function complete() {

    let date = $('#modalDate').val();

    $('#tcomplete').dataTable({
        destroy: true,
        ajax: `../src/controllers/capacity.ajax.php?opc=3&date=${date}`,
        info: false,
        lengthChange: false,
        ordering: false,
        pageLength: 23,
        pagingType: 'full_numbers',
        searching: false,
        columnDefs: [
            {className: "dt-center", targets: ["_all"]}
        ]
    });

    $('#Tcomplete').modal('show');
}

function incomplete() {

    let date = $('#modalDate').val();

    $('#tincomplete').dataTable({
        destroy: true,
        ajax: `../src/controllers/capacity.ajax.php?opc=4&date=${date}`,
        info: false,
        lengthChange: false,
        ordering: false,
        pageLength: 23,
        pagingType: 'full_numbers',
        searching: false,
        columnDefs: [
            {className: "dt-center", targets: ["_all"]}
        ]
    });

    $('#Tincomplete').modal('show');
}