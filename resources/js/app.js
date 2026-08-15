document.addEventListener('alpine:init', () => {
    Alpine.data('addressAutocomplete', () => ({
        attach(container) {
            const start = () => {
                const input = container?.querySelector('input');

                if (!input || !window.google?.maps?.places) {
                    return;
                }

                const autocomplete = new google.maps.places.Autocomplete(input, {
                    fields: ['formatted_address'],
                    componentRestrictions: { country: 'us' },
                });

                autocomplete.addListener('place_changed', () => {
                    const place = autocomplete.getPlace();

                    if (place.formatted_address) {
                        this.$wire.set('address', place.formatted_address);
                    }
                });
            };

            if (window.google?.maps?.places) {
                start();
            } else {
                window.addEventListener('google-maps-ready', start, { once: true });
            }
        },
    }));
});
