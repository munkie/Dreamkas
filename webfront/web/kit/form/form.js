define(function(require) {
    //requirements
    var Block = require('kit/block'),
        form2js = require('form2js'),
        router = require('router'),
        _ = require('lodash');

    return Block.extend({
        model: null,
        collection: null,
        redirectUrl: null,
        events: {
            'change input, checkbox, textarea': function() {
                var block = this;

                block.removeSuccessMessage();
            },
            submit: function(e) {
                e.preventDefault();

                var block = this,
                    submit;

                block.formData = block.getData();

                block.submitStart();
                block.trigger('submit:start');

                Promise.resolve(block.submit()).then(function(){

                    block.submitSuccess();
                    block.trigger('submit:success');

                    block.submitComplete();
                    block.trigger('submit:complete');

                }, function(response){

                    block.submitError(response);
                    block.trigger('submit:error', response);

                    block.submitComplete(response);
                    block.trigger('submit:complete', response);

                });
            }
        },
        elements: {
            $submitButton: function(){
                var block = this;

                return $(block.el).find('[type="submit"]').closest('.button').add('[form="' + block.el.id + '"]');
            },
            controls: '.form__controls',
            results: '.form__results'
        },
        initialize: function(){
            var block = this;

            Block.prototype.initialize.apply(block, arguments);

            block.model = block.get('model');
            block.redirectUrl = block.get('redirectUrl');
        },
        getData: function(){
            return form2js(this.el, '.', false);
        },
        submit: function() {
            var block = this;

            return block.model.save(block.formData);
        },
        submitStart: function() {
            var block = this;

            block.elements.$submitButton.addClass('preloader_stripes');
            block.disable(true);
            block.removeErrors();
            block.removeSuccessMessage();
        },
        submitComplete: function() {
            var block = this;

            block.elements.$submitButton.removeClass('preloader_stripes');
            block.disable(false);
        },
        submitSuccess: function() {
            var block = this;

            if (block.collection) {
                block.collection.push(block.model);
            }

            if (block.redirectUrl) {
                router.navigate(block.redirectUrl);
                return;
            }

            if (block.get('successMessage')) {
                block.showSuccessMessage();
            }
        },
        submitError: function(response) {
            var block = this;
            block.showErrors(JSON.parse(response.responseText), response);
        },
        showErrors: function(errors, response) {
            var block = this;

            function addErrorToInput(data, field, prefix) {
                prefix = prefix || '';

                var fieldErrors,
                    $input = $(block.el).find('[name="' + prefix + field + '"]:visible'),
                    $field = $input.closest('.form__field');

                if (data.errors) {
                    $input.addClass('inputText_error');
                    fieldErrors = data.errors.join('. ');
                    $field.attr('data-error', ($field.attr('data-error') ? $field.attr('data-error') + ', ' : '') + block.getText(fieldErrors));
                }

                if (data.children) {
                    var newPrefix = prefix + field + '.';
                    _.each(data.children, function(data, field) {
                        addErrorToInput(data, field, newPrefix);
                    });
                }
            }

            block.removeErrors();

            if (errors.children) {
                _.each(errors.children, function(data, field) {
                    addErrorToInput(data, field);
                });
            }

            if (errors.error) {
                block.elements.controls.dataset.error = typeof errors.error === 'string' ? block.getText(errors.error) : block.getText('неизвестная ошибка: ' + response.statusText);
            }

            if (errors.errors && errors.errors.length) {
                block.elements.controls.dataset.error = errors.errors.join(', ');
            }

            if (errors.description) {
                block.elements.controls.dataset.error = block.getText(errors.description);
            }

            if (errors.error_description) {
                block.elements.controls.dataset.error = block.getText(errors.error_description);
            }
        },
        removeErrors: function() {
            var block = this;
            $(block.el).find('[data-error]').removeAttr('data-error');
            $(block.el).find('.inputText_error').removeClass('inputText_error');
        },
        showSuccessMessage: function() {
            var block = this;

            block.elements.$submitButton.after('<span class="form__successMessage">' + block.getText(block.get('successMessage')) + '</span>')
        },
        removeSuccessMessage: function() {
            var block = this;

            $(block.el).find('.form__successMessage').remove();
        },
        disable: function(disabled) {
            var block = this;
            if (disabled) {
                block.elements.$submitButton.attr('disabled', true);
            } else {
                block.elements.$submitButton.removeAttr('disabled');
            }
        },
        clear: function() {
            var block = this;

            block.removeErrors();
            block.elements.$submitButton.removeClass('preloader preloader_stripes');

            $(block.el).find(':input').each(function() {
                switch (this.type) {
                    case 'password':
                    case 'select-multiple':
                    case 'select-one':
                    case 'text':
                    case 'textarea':
                    case 'hidden':
                        $(this).val('');
                        break;
                    case 'checkbox':
                    case 'radio':
                        this.checked = false;
                }
            });
        }
    })
});