// En tu archivo app.js u otro archivo principal
import 'select2'; // Importa Select2

// Puedes inicializar Select2 en elementos específicos
$(document).ready(function () {

    $('.select2').select2({
        width: '100%',
    });

    $('.select2-users').select2({
        width: '100%',
        ajax: {
            url: '/user/search/ajax',
            dataType: 'json',
            delay: 250, // Add a delay to prevent overloading the server
            data: function (params) {
                return {
                    search: params.term, // Send the search term
                    page: params.page || 1 // Handle pagination if needed
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
            
                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 10) < data.count_filtered
                    }
                };
            }
        },
    });
});
