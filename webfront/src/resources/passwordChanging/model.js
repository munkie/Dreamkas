define(function(require, exports, module) {
    //requirements
    var Model = require('kit/model/model');

    return Model.extend({
        url: Model.baseApiUrl + '/users/current/changePassword',
        saveData: [
            'password',
            'newPassword'
        ],
        isNew: function() {
            return true;
        }
    });
});
