import 'datatables.net-bs5';
import 'datatables.net-buttons-bs5';
import jsZip from 'jszip';
import 'datatables.net-buttons/js/buttons.html5';
import 'datatables.net-buttons/js/buttons.print';

import pdfMake from "pdfmake/build/pdfmake";
import pdfFonts from "pdfmake/build/vfs_fonts";

pdfMake.vfs = pdfFonts.vfs;
window.JSZip = jsZip;

window.addEventListener("load", function () {
    $('.datatable-basic').DataTable({
        language: { 
            url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json',
            paginate: {
                first: "«",
                last: "»",
                next: "›",
                previous: "‹"
            },
        },
    });

    $('.datatable-export').DataTable({
        language: { 
            url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json',
            paginate: {
                first: "«",
                last: "»",
                next: "›",
                previous: "‹"
            },
        },
        layout: {
            topStart: 'pageLength',
            top2Start: {
                buttons: ['copy', 'csv', 'excel', 'print']
            }
        }
    });

    $('.datatable-filters').DataTable({
        language: { 
            url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json',
            paginate: {
                first: "«",
                last: "»",
                next: "›",
                previous: "‹"
            },
        },

        initComplete: function () {
            // Add search inputs to each column header
            this.api()
            .columns()
            .every(function () {
                let column = this;
                let title = column.header().textContent;
 
                // Create input element
                let input = document.createElement('input');
                input.setAttribute('class', 'form-control form-control-sm mt-2');
                input.placeholder = 'Filtrar '+title+'...';
                column.header().appendChild(input);
 
                // Event listener for user input
                input.addEventListener('keyup', () => {
                    if (column.search() !== this.value) {
                        column.search(input.value).draw();
                    }
                });

                // Event listener to prevent sort by clicking input
                input.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });
        },
    });

    $('.datatable-basic-eng').DataTable({
        
    });

    $('.datatable-users').DataTable({
        autoWidth: false,
        columnDefs: [
            { name: "id", width: "10%", "targets": 0 },
            { name: "email", width: "60%", "targets": 1 },
            { name: "Actions", width: "30%", "targets": 2, orderable: false, searchable: false },
        ],
        processing: true,
        serverSide: true,
        ajax: {
            url: '/user/ajax',
            type: 'POST',
            data: function (d) {
                d.getters = ['getId', 'getEmail'];
                d.buttons = [
                    { path: 'app_user_show', class: 'btn btn-xs btn-info', label: 'ver' },
                    { path: 'app_user_edit', class: 'btn btn-xs btn-secondary', label: 'editar', icon: 'edit' },
                    { path: 'app_user_impersonate', class: 'btn btn-xs btn-danger', label: 'personificar', icon: 'people_alt' }
                ];
            }
        },

        language: { 
            url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json',
            paginate: {
                first: "«",
                last: "»",
                next: "›",
                previous: "‹"
            },
        },
    });
});