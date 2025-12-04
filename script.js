jQuery(document).ready(function($) {

    // --- 1. Modal Logic ---

    // Open Modal
    $('#apaf-advanced-filters-trigger').on('click', function(e) {
        e.preventDefault();
        $('#apaf-modal').css('display', 'flex');
    });

    // Close Modal (X button)
    $('#apaf-modal-close').on('click', function() {
        $('#apaf-modal').hide();
    });

    // Close Modal (Overlay click)
    $('.apaf-modal-overlay').on('click', function() {
        $('#apaf-modal').hide();
    });

    // Apply Filters (Close Modal + optional logic)
    $('#apaf-apply-filters').on('click', function() {
        $('#apaf-modal').hide();
        // Here you could trigger a form submit or just update UI
        // For now, we assume the user will click "Buscar" on the main bar,
        // or this button itself could trigger the form submit if it was inside the form proper but outside the modal scope?
        // Actually the modal is INSIDE the form in our HTML structure, so inputs are part of the form.
        // We can just close the modal.
    });


    // --- 2. AJAX City -> Neighborhood ---

    $('#apaf-city').on('change', function() {
        var city = $(this).val();
        var $neighborhoodSelect = $('#apaf-neighborhood');

        // Reset
        $neighborhoodSelect.html('<option value="">Carregando...</option>').prop('disabled', true);

        if (city) {
            $.ajax({
                url: apaf_obj.ajaxurl,
                type: 'GET',
                data: {
                    action: 'apaf_get_neighborhoods',
                    city: city,
                    nonce: apaf_obj.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var options = '<option value="">Bairro</option>';
                        $.each(response.data, function(slug, name) {
                            options += '<option value="' + slug + '">' + name + '</option>';
                        });
                        $neighborhoodSelect.html(options).prop('disabled', false);
                    } else {
                        $neighborhoodSelect.html('<option value="">Erro ao carregar</option>');
                    }
                },
                error: function() {
                    $neighborhoodSelect.html('<option value="">Erro</option>');
                }
            });
        } else {
            $neighborhoodSelect.html('<option value="">Bairro</option>').prop('disabled', true);
        }
    });


    // --- 3. Spec Buttons Logic ---

    $('.spec-btn').on('click', function() {
        var $this = $(this);
        var value = $this.data('value');
        var $row = $this.closest('.apaf-specs-row');
        var inputSelector = $row.data('input');
        var $input = $(inputSelector);

        // Toggle Active Class
        // If we want single selection per row:
        $row.find('.spec-btn').removeClass('active');
        $this.addClass('active');

        // Update Hidden Input
        $input.val(value);
    });

});
