define(function(require) {
        //requirements
        var Model = require('kit/model/model');

        return Model.extend({
            urlRoot: Model.baseApiUrl + '/stores',
            saveData: [
                'name',
                'address',
                'contacts'
            ]
        });
    }
);