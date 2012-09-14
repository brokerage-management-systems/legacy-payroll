/**
 *
 */

function ORUpdateObject()
{
    this.brokerList = [];
    this.brokerOverrideList = [];
}
ORUpdateObject.prototype.getForm = function()
{
    var form = '<table id="rep-structure-update-table"><tr>';
    if (this.brokerOverrideList.length > 0) {
        for (var i=0;i<this.brokerOverrideList.length;i++) {
            for (var j=0;j<this.brokerList.length;j++) {
                if ($('select[name=brokerNumber]').val() == this.brokerList[j].repNumber) {
                    form += '<td>'+this.brokerList[j].repNumber+' - '+this.brokerList[j].firstName+' '+this.brokerList[j].lastName+' Override of: </td>';
                    break;
                }
            }
            form += '<td><input type="text" name="splitPercent" value="'+(this.brokerOverrideList[i].splitPercent * 100)+'" size=5 /> % on: </td>';
            form += '<td><select name="altRep">';
            for (var j=0;j<this.brokerList.length;j++) {
                if ($('select[name=brokerNumber]').val() == this.brokerList[j].repNumber) {
                    // Cannot add override to yourself
                } else {
                    if (this.brokerOverrideList[i].altRep == this.brokerList[j].repNumber) {
                        form += '<option selected="selected" value="'+this.brokerList[j].repNumber+'">'+this.brokerList[j].repNumber+' - '+this.brokerList[j].firstName+' '+this.brokerList[j].lastName+'</option>';
                    } else {
                        form += '<option value="'+this.brokerList[j].repNumber+'">'+this.brokerList[j].repNumber+' - '+this.brokerList[j].firstName+' '+this.brokerList[j].lastName+'</option>';
                    }
                }
            }
            form += '</select></td>';
            form += '<td><button name="rep-structure-update-override" value="'+i+'">^</button></td>';
            form += '<td><button name="rep-structure-delete-override" value="'+i+'">-</button></td>';
            if (i==this.brokerOverrideList.length-1 && this.brokerOverrideList[i].id != null) {
                form += '<td><button name="rep-structure-add-override" value="'+i+'">+</button></td>';
            }
            form += '</tr>';
        }
    } else {
        var emptyOverride = {};
        emptyOverride.id = null;
        emptyOverride.mainRep = '';
        emptyOverride.altRep = '';
        splitPercent = ' ';
        this.brokerOverrideList.push(emptyOverride);
        for (var j=0;j<this.brokerList.length;j++) {
            if ($('select[name=brokerNumber]').val() == this.brokerList[j].repNumber) {
                form += '<td>'+this.brokerList[j].repNumber+' - '+this.brokerList[j].firstName+' '+this.brokerList[j].lastName+'</td>';
                break;
            }
        }
        form += '<td><input type="text" name="splitPercent" value="'+(this.brokerOverrideList[0].splitPercent * 100)+'" size=5 /></td>';
        form += '<td><select name="altRep">';
        for (var j=0;j<this.brokerList.length;j++) {
            if ($('select[name=brokerNumber]').val() == this.brokerList[j].repNumber) {
                // Cannot add override to yourself
            } else {
                form += '<option value="'+this.brokerList[j].repNumber+'">'+this.brokerList[j].repNumber+' - '+this.brokerList[j].firstName+' '+this.brokerList[j].lastName+'</option>';
            }
        }
        form += '</select></td>';
        form += '<td><button name="rep-structure-update-override" value="0">^</button></td></tr>';
    }
    form += '</table>';
    return form;
};
ORUpdateObject.prototype.setBrokerList = function()
{
    var self = this;
    dbw.table = 'Brokers';
    dbw.getByFields({active:1}, function(data) {
        self.brokerList = data;
    });
};
ORUpdateObject.prototype.setBrokerOverrideList = function()
{
    var self = this;
    dbw.table = 'repNums';
    dbw.getByFields({type:'Override',mainRep:$('select[name=brokerNumber]').val()}, function(data) {
        self.brokerOverrideList = data;
    });
};
ORUpdateObject.prototype.isValidPercent = function(value)
{
    return isNaN(parseFloat(value));
};
ORUpdateObject.prototype.simpleModalObject = function(title, callback)
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
    var orObj = new ORUpdateObject();
    
    var $overrideDialog = $('<div></div>').dialog({
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

    $('#rep-structure-new-override').live('click', function() {
        orObj.method = 'insert';
        orObj.setBrokerList();
        orObj.setBrokerOverrideList();
        $overrideDialog.html(orObj.getForm());
        $overrideDialog.dialog('open');
        return false;
    });
    
    $('#rep-structure-edit-override').live('click', function() {
        orObj.method = 'update';
        orObj.setBrokerList();
        orObj.setBrokerOverrideList();
        $overrideDialog.html(orObj.getForm());
        $overrideDialog.dialog('open');
        return false;
    });
    
    $('button[name=rep-structure-update-override]').live('click', function() {
        var index = $(this).val();
        var params = {};
        params.mainRep = $('select[name=brokerNumber]').val();
        params.altRep = $('#rep-structure-update-table tr').find('select[name=altRep] option:selected').get(index).value;
        params.type = 'Override';
        params.splitPercent = ($('#rep-structure-update-table tr').find('input[name=splitPercent]').get(index).value / 100);
        if (orObj.isValidPercent(params.splitPercent)) {
            var dialog = orObj.simpleModalObject('Submission Error',null);
            dialog.html('Please enter a valid percent.');
            dialog.dialog('open');
            return false;
        }
        console.dir(params);
        if (orObj.brokerOverrideList[index].id != null) {
            params.id = orObj.brokerOverrideList[index].id;
            dbw.table  = 'repNums';
            dbw.updateObject(params, function(data) {
                console.dir(data);
            });
        } else {
            dbw.table  = 'repNums';
            dbw.insertObject(params, function(data) {
                console.dir(data);
            });
        }
        orObj.setBrokerOverrideList();
        $overrideDialog.html(orObj.getForm());
        $overrideDialog.dialog('open');
        return false;
    });
    
    $('button[name=rep-structure-delete-override]').live('click', function() {
        var index = $(this).val();
        if (orObj.brokerOverrideList[index].id != null) {
            dbw.table  = 'repNums';
            dbw.deleteObject(orObj.brokerOverrideList[index].id, function(data) {
                console.dir(data);
            });
            orObj.setBrokerOverrideList();
            $overrideDialog.html(orObj.getForm());
            $overrideDialog.dialog('open');
            return false;
        }
    });
    
    $('button[name=rep-structure-add-override]').live('click', function() {
        var index = $(this).val();
        var emptyOverride = {};
        emptyOverride.id = null;
        emptyOverride.mainRep = '';
        emptyOverride.altRep = '';
        splitPercent = ' ';
        orObj.brokerOverrideList.push(emptyOverride);
        $overrideDialog.html(orObj.getForm());
        $overrideDialog.dialog('open');
        return false;
    });
    
});