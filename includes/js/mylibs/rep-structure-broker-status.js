/**
 *
 */

function BSUpdateObject()
{
    this.broker = {};
}
BSUpdateObject.prototype.getForm = function()
{
    var form = '<table id="rep-structure-update-table"><tr>';
    if (this.broker) {
        form += '<th>'+$('select[name=brokerNumber] option:selected').text()+'</th></tr><tr>';
        form += '<td>Broker#: <input type="text" name="repNumber" value="'+this.broker.repNumber+'" size=4 /></td>';
        form += '<td>First Name: <input type="text" name="firstName" value="'+this.broker.firstName+'" size=15 /></td>';
        form += '<td>Last Name: <input type="text" name="lastName" value="'+this.broker.lastName+'" size=15 /></td>';
        form += '<td>Status: <select name="active">'
        if (this.broker.active == 0) {
            form += '<option selected="selected" value=0>Inactive</option>';
            form += '<option value=1>Active</option>';
        } else {
            form += '<option value=0>Inactive</option>';
            form += '<option selected="selected" value=1>Active</option>';
        }
        form += '</select></td>';
        form += '<td><button name="rep-structure-update-broker" value=0>^</button></td>';
        form += '</tr>';
    } else {
        var emptyBroker = {};
        emptyBroker.id = null;
        emptyBroker.repNumber = '';
        emptyBroker.firstName = '';
        emptyBroker.lastName = '';
        emptyBroker.active = '';
        this.broker = emptyBroker;
        form += '<td>Broker#: <input type="text" name="repNumber" value="'+this.broker.repNumber+'" size=4 /></td>';
        form += '<td>First Name: <input type="text" name="firstName" value="'+this.broker.firstName+'" size=15 /></td>';
        form += '<td>Last Name: <input type="text" name="lastName" value="'+this.broker.lastName+'" size=15 /></td>';
        form += '<td>Status: <select name="active">'
        if (this.broker.active == 0) {
            form += '<option selected="selected" value=0>Inactive</option>';
            form += '<option value=1>Active</option>';
        } else {
            form += '<option value=0>Inactive</option>';
            form += '<option selected="selected" value=1>Active</option>';
        }
        form += '</select></td>';
        form += '<td><button name="rep-structure-update-broker" value=0>^</button></td>';
        form += '</tr>';
    }
    form += '</table>';
    return form;
};
BSUpdateObject.prototype.setBroker = function()
{
    var self = this;
    dbw.table = 'Brokers';
    dbw.getByFields({repNumber:$('select[name=brokerNumber]').val()}, function(data) {
        self.broker = data[0];
    });
};
BSUpdateObject.prototype.isValidPercent = function(value)
{
    return isNaN(parseFloat(value));
};
BSUpdateObject.prototype.simpleModalObject = function(title, callback)
{
    var self = this;
    return $('<div></div>').dialog({
        autoOpen: false,
        buttons: {
            'Ok': function() {
                $(this).dialog('close');
                if (callback != null) {
                    callback();
                }
            }
        },
        height: 'auto',
        modal: true,
        title: title,
        width: 'auto'
    });
};

$(document).ready(function()
{  
    dbw = new DbWrapper();
    var bsObj = new BSUpdateObject();
    
    var $brokerDialog = $('<div></div>').dialog({
        autoOpen: false,
        buttons: {
            "Ok": function(submitData) {
                $(this).dialog("close");
                location.reload(true);
            }
        },
        height: 'auto',
        modal: true,
        title: 'Update Broker Structure',
        width: 'auto'
    });

    $('#rep-structure-new-broker').live('click', function() {
        bsObj.method = 'insert';
        //bsObj.setBroker();
        bsObj.broker = null;
        $brokerDialog.html(bsObj.getForm());
        $brokerDialog.dialog('open');
        return false;
    });
    
    $('#rep-structure-edit-broker').live('click', function() {
        bsObj.method = 'update';
        bsObj.setBroker();
        $brokerDialog.html(bsObj.getForm());
        $brokerDialog.dialog('open');
        return false;
    });
    
    $('button[name=rep-structure-update-broker]').live('click', function() {
        var index = $(this).val();
        var params = {};
        params.repNumber = $('#rep-structure-update-table tr').find('input[name=repNumber]').get(index).value;
        params.firstName = $('#rep-structure-update-table tr').find('input[name=firstName]').get(index).value;
        params.lastName = $('#rep-structure-update-table tr').find('input[name=lastName]').get(index).value;
        params.active = $('select[name=active]').val();
        console.dir(params);
        if (bsObj.broker.userid != null) {
            params.id = bsObj.broker.userid;
            dbw.table  = 'Brokers';
            dbw.updateObject(params, function(data) {
                console.dir(data);
                var $resultDialog = bsObj.simpleModalObject('Update Broker Results',function(){location.reload(true)});
                if (data == true) {
                    $resultDialog.html('Successfully Updated.');
                } else {
                    $resultDialog.html('Something went wrong!');
                }
                $resultDialog.dialog('open');
            });
        } else {
            dbw.table  = 'Brokers';
            dbw.insertObject(params, function(data) {
                console.dir(data);
                var $resultDialog = bsObj.simpleModalObject('Create Broker Results',function(){location.reload(true)});
                if (data != null) {
                    $resultDialog.html('Successfully Inserted.');
                } else {
                    $resultDialog.html('Something went wrong!');
                }
                $resultDialog.dialog('open');
            });
        }
        bsObj.setBroker();
        $brokerDialog.html(bsObj.getForm());
        $brokerDialog.dialog('open');
        return false;
    });
    
});