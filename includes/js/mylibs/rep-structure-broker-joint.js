/**
 *
 */

function JRUpdateObject()
{
    this.brokerList = [];
    this.brokerJointRepList = [];
}
JRUpdateObject.prototype.getForm = function()
{
    var form = '<table id="rep-structure-update-table"><tr>';
    if (this.brokerJointRepList.length > 0) {
        for (var i=0;i<this.brokerJointRepList.length;i++) {
            for (var j=0;j<this.brokerList.length;j++) {
                if ($('select[name=brokerNumber]').val() == this.brokerList[j].repNumber) {
                    form += '<td>'+this.brokerList[j].repNumber+' - '+this.brokerList[j].firstName+' '+this.brokerList[j].lastName+'</td>';
                    break;
                }
            }
            form += '<td>, JointRep with: <select name="altRep">';
            for (var j=0;j<this.brokerList.length;j++) {
                if ($('select[name=brokerNumber]').val() == this.brokerList[j].repNumber) {
                    //
                } else {
                    if (this.brokerJointRepList[i].altRep == this.brokerList[j].repNumber) {
                        form += '<option selected="selected" value="'+this.brokerList[j].repNumber+'">'+this.brokerList[j].repNumber+' - '+this.brokerList[j].firstName+' '+this.brokerList[j].lastName+'</option>';
                    } else {
                        form += '<option value="'+this.brokerList[j].repNumber+'">'+this.brokerList[j].repNumber+' - '+this.brokerList[j].firstName+' '+this.brokerList[j].lastName+'</option>';
                    }
                }
            }
            form += '</select></td>';
            form += '<td><input type="text" name="splitPercent" value="'+this.brokerJointRepList[i].splitPercent+'" size=4 /></td>';
            form += '<td><button name="rep-structure-update-joint" value="'+i+'">^</button></td>';
            form += '<td><button name="rep-structure-delete-joint" value="'+i+'">-</button></td>';
            if (i==this.brokerJointRepList.length-1 && this.brokerJointRepList[i].id != null) {
                form += '<td><button name="rep-structure-add-joint" value="'+i+'">+</button></td>';
            }
            form += '</tr>';
        }
    } else {
        var emptyJointRep = {};
        emptyJointRep.id = null;
        emptyJointRep.mainRep = '';
        emptyJointRep.altRep = '';
        splitPercent = ' ';
        this.brokerJointRepList.push(emptyJointRep);
        for (var j=0;j<this.brokerList.length;j++) {
            if ($('select[name=brokerNumber]').val() == this.brokerList[j].repNumber) {
                form += '<td>'+this.brokerList[j].repNumber+' - '+this.brokerList[j].firstName+' '+this.brokerList[j].lastName+'</td>';
                break;
            }
        }
        form += '<td>, JointRep with: <select name="altRep">';
        for (var j=0;j<this.brokerList.length;j++) {
            if ($('select[name=brokerNumber]').val() == this.brokerList[j].repNumber) {
                //
            } else {
                form += '<option value="'+this.brokerList[j].repNumber+'">'+this.brokerList[j].repNumber+' - '+this.brokerList[j].firstName+' '+this.brokerList[j].lastName+'</option>';
            }
        }
        form += '</select></td>';
        form += '<td> split: <input type="text" name="splitPercent" value="'+this.brokerJointRepList[0].splitPercent+'" size=4 /></td>';
        form += '<td><button name="rep-structure-update-joint" value="0">^</button></td></tr>';
    }
    form += '</table>';
    return form;
};
JRUpdateObject.prototype.setBrokerList = function()
{
    var self = this;
    dbw.table = 'Brokers';
    dbw.getByFields({active:1}, function(data) {
        for (var i=0;i<data.length;i++) {
            if (data[i].firstName == 'JointRep' || data[i].repNumber == $('select[name=brokerNumber]').val()) {
                self.brokerList.push(data[i]);
            }
        }
    });
};
JRUpdateObject.prototype.setbrokerJointRepList = function()
{
    var self = this;
    dbw.table = 'repNums';
    dbw.getByFields({type:'JointRep',mainRep:$('select[name=brokerNumber]').val()}, function(data) {
        self.brokerJointRepList = data;
    });
};
JRUpdateObject.prototype.isValidPercent = function(value)
{
    return isNaN(parseFloat(value));
};
JRUpdateObject.prototype.simpleModalObject = function(title, callback)
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
    var jrObj = new JRUpdateObject();
    
    var $jointRepDialog = $('<div></div>').dialog({
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

    $('#rep-structure-new-joint').live('click', function() {
        jrObj.method = 'insert';
        jrObj.setBrokerList();
        jrObj.setbrokerJointRepList();
        $jointRepDialog.html(jrObj.getForm());
        $jointRepDialog.dialog('open');
        return false;
    });
    
    $('#rep-structure-edit-joint').live('click', function() {
        jrObj.method = 'update';
        jrObj.setBrokerList();
        jrObj.setbrokerJointRepList();
        $jointRepDialog.html(jrObj.getForm());
        $jointRepDialog.dialog('open');
        return false;
    });
    
    $('button[name=rep-structure-update-joint]').live('click', function() {
        var index = $(this).val();
        var params = {};
        params.mainRep = $('select[name=brokerNumber]').val();
        params.altRep = $('#rep-structure-update-table tr').find('select[name=altRep] option:selected').get(index).value;
        params.type = 'JointRep';
        params.splitPercent = ($('#rep-structure-update-table tr').find('input[name=splitPercent]').get(index).value);
        if (jrObj.isValidPercent(params.splitPercent)) {
            var dialog = jrObj.simpleModalObject('Submission Error',null);
            dialog.html('Please enter a valid split.');
            dialog.dialog('open');
            return false;
        }
        console.dir(params);
        if (jrObj.brokerJointRepList[index].id != null) {
            params.id = jrObj.brokerJointRepList[index].id;
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
        jrObj.setbrokerJointRepList();
        $jointRepDialog.html(jrObj.getForm());
        $jointRepDialog.dialog('open');
        return false;
    });
    
    $('button[name=rep-structure-delete-joint]').live('click', function() {
        var index = $(this).val();
        if (jrObj.brokerJointRepList[index].id != null) {
            dbw.table  = 'repNums';
            dbw.deleteObject(jrObj.brokerJointRepList[index].id, function(data) {
                console.dir(data);
            });
            jrObj.setbrokerJointRepList();
            $jointRepDialog.html(jrObj.getForm());
            $jointRepDialog.dialog('open');
            return false;
        }
    });
    
    $('button[name=rep-structure-add-joint]').live('click', function() {
        var index = $(this).val();
        var emptyJointRep = {};
        emptyJointRep.id = null;
        emptyJointRep.mainRep = '';
        emptyJointRep.altRep = '';
        splitPercent = ' ';
        jrObj.brokerJointRepList.push(emptyJointRep);
        $jointRepDialog.html(jrObj.getForm());
        $jointRepDialog.dialog('open');
        return false;
    });
    
});