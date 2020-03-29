$.notify = {
        queue: {},
        settings: {
            'errorsimemp': {
                'sticky': true,
                'type': 'errorsimemp'
            },
        create: function (text, options) {
            return $("<span />", { text : text }).notify(options);
        }
    }
};