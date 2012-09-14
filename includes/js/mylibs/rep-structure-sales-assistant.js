/**
 * 
 */

function SAUpdateObject()
{
    this.salesAssistant = {};
    this.salesAssistantList = [];
}
SAUpdateObject.prototype.getForm = function()
{
    var saId = this.salesAssistant.id?this.salesAssistant.id:null;
    var form = '<form id="rep-structure-sa-bonus-submission" name="editSABonus" action="" method="GET" >';
    form += '<input type="hidden" name="repNum" value="'+$('select[name=brokerNumber]').val()+'" />';
    //form += '<input type="hidden" name="recordId" value="'+this.salesAssistant.id+'" />';
    form += '<input type="hidden" name="recordId" value="'+saId+'" />';
    form += '<h4>Edit Structure</h4><table><tr><td></td></tr>';
    form += '<tr><th>Sales Assistant Bonus</th></tr>';
    form += '<tr><td>Sales Assistant: <select name="saToUpdate">';
    for (var i=0;i<this.salesAssistantList.length;i++) {
        if (this.salesAssistant.salesAssistantId == this.salesAssistantList[i].id) {
            form += '<option selected="selected" value="'+this.salesAssistantList[i].id+'">'+this.salesAssistantList[i].firstName+' '+this.salesAssistantList[i].lastName+'</option>';
        } else {
            form += '<option value="'+this.salesAssistantList[i].id+'">'+this.salesAssistantList[i].firstName+' '+this.salesAssistantList[i].lastName+'</option>';
        }
    }
    console.dir(this.salesAssistant);
    form += '</select> Bonus: <input type="text" name="percent" size="3" value="'+(this.salesAssistant.percent * 100)+'" />% </td></tr>';
    form += '</table></form>';
    return form;
};
SAUpdateObject.prototype.setSalesAssistant = function()
{
    var self = this;
    dbw.table = 'salesAssistantData';
    dbw.getByFields({repNum: $('select[name=brokerNumber]').val()}, function(data) {
        self.salesAssistant = data[0];
    });
};
SAUpdateObject.prototype.setSalesAssistantList = function()
{
    var self = this;
    dbw.table = 'SalesAssistant';
    dbw.getAll({}, function(data) {
        self.salesAssistantList = data;
    });
};
SAUpdateObject.prototype.isValidatePercent = function(value)
{
    return isNaN(parseFloat(value));
};
SAUpdateObject.prototype.simpleModalObject = function(title, callback)
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

$(document).ready(function() {  
    dbw = new DbWrapper();
    var saObj = new SAUpdateObject();
    
    var $saBonusDialog = $('<div></div>').dialog({
        autoOpen: false,
        buttons: {
            "Submit": function(submitData) {                
                var params = {};
                params.id = $('input[name=recordId]').val();
                params.salesAssistantId = $('select[name=saToUpdate]').val();
                params.saName = $('select[name=saToUpdate] option:selected').text();
                params.percent = ($('input[name=percent]').val() / 100);
                params.repNum = $('input[name=repNum]').val();
                if (saObj.isValidatePercent(params.percent)) {
                    var $errorDialog = saObj.simpleModalObject('Form Submission Error');
                    $errorDialog.html('Please enter a valid percentage.');
                    $errorDialog.dialog('open');
                    return false;
                }
                dbw.table = 'salesAssistantData';
                if (saObj.method == 'insert') {
                    dbw.insertObject(params, function(data) {
                        var $resultDialog = saObj.simpleModalObject('Edit Bonus Results',function(){location.reload(true)});
                        if (data != null) {
                            $resultDialog.html('Successfully Inserted.');
                        } else {
                            $resultDialog.html('Something went wrong!');
                        }
                        $resultDialog.dialog('open');
                    });
                } else if (saObj.method == 'update') {
                    dbw.updateObject(params, function(data) {
                        var $resultDialog = saObj.simpleModalObject('Edit Bonus Results',function(){location.reload(true)});
                        if (data != null) {
                            $resultDialog.html('Successfully Inserted.');
                        } else {
                            $resultDialog.html('Something went wrong!');
                        }
                        $resultDialog.dialog('open');
                    });
                }
                $(this).dialog("close");
            },
            "Cancel": function() {
                $(this).dialog("close");
            }
        },
        height: 'auto',
        modal: true,
        title: 'Update Broker Structure',
        width: 'auto'
    });

    $('#rep-structure-insert-sa-bonus').live('click', function() {
        saObj.method = 'insert';
        saObj.setSalesAssistantList();
        $saBonusDialog.html(saObj.getForm());
        $saBonusDialog.dialog('open');
        return false;
    });
    
    $('#rep-structure-update-sa-bonus').live('click', function() {
        saObj.method = 'update';
        saObj.setSalesAssistant();
        saObj.setSalesAssistantList();
        $saBonusDialog.html(saObj.getForm());
        $saBonusDialog.dialog('open');
        return false;
    });
    
});