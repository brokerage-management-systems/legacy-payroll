/**
 *
 */

function PPUpdateObject()
{
    this.payoutPercentList = [];
}
PPUpdateObject.prototype.getForm = function()
{
    var form = '<table id="rep-structure-update-table"><tr>';
    if (this.payoutPercentList.length > 0) {
        for (var i=0;i<this.payoutPercentList.length;i++) {
            form += '<td>'+$('select[name=brokerNumber] option:selected').text()+'</td>';
            form += '<td>commission &lt; <input type="text" name="minAmt" value="'+this.payoutPercentList[i].minAmt+'" size=8 /></td>';
            form += '<td> AND &gt; <input type="text" name="maxAmt" value="'+this.payoutPercentList[i].maxAmt+'" size=8 /></td>';
            form += '<td>payout is: <input type="text" name="payPercent" value="'+(this.payoutPercentList[i].payPercent * 100)+'" size=4 />%</td>';
            form += '<td><button name="rep-structure-update-payout" value="'+i+'">^</button></td>';
            form += '<td><button name="rep-structure-delete-payout" value="'+i+'">-</button></td>';
            if (i==this.payoutPercentList.length-1 && this.payoutPercentList[i].id != null) {
                form += '<td><button name="rep-structure-add-payout" value="'+i+'">+</button></td>';
            }
            form += '</tr>';
        }
    } else {
        var emptyPayout = {};
        emptyPayout.id = null;
        emptyPayout.repNum = '';
        emptyPayout.minAmt = '';
        emptyPayout.maxAmt = '';
        emptyPayout.payPercent = '';
        this.payoutPercentList.push(emptyPayout);
        form += '<td>'+$('select[name=brokerNumber] option:selected').text()+'</td>';
        form += '<td>commission &lt; <input type="text" name="minAmt" value="'+this.payoutPercentList[0].minAmt+'" size=8 /></td>';
        form += '<td> AND &gt; <input type="text" name="maxAmt" value="'+this.payoutPercentList[0].maxAmt+'" size=8 /></td>';
        form += '<td>payout is: <input type="text" name="payPercent" value="'+(this.payoutPercentList[0].payPercent * 100)+'" size=4 />%</td>';
        form += '<td><button name="rep-structure-update-payout" value="0">^</button></td></tr>';
    }
    form += '</table>';
    return form;
};
PPUpdateObject.prototype.setPayoutPercentList = function()
{
    var self = this;
    dbw.table = 'payoutStructure';
    dbw.getByFields({repNum:$('select[name=brokerNumber]').val()}, function(data) {
        self.payoutPercentList = data;
    });
};
PPUpdateObject.prototype.isValidPercent = function(value)
{
    return isNaN(parseFloat(value));
};
PPUpdateObject.prototype.simpleModalObject = function(title, callback)
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
    var ppObj = new PPUpdateObject();
    
    var $payoutDialog = $('<div></div>').dialog({
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

    $('#rep-structure-new-payout').live('click', function() {
        ppObj.method = 'insert';
        ppObj.setPayoutPercentList();
        $payoutDialog.html(ppObj.getForm());
        $payoutDialog.dialog('open');
        return false;
    });
    
    $('#rep-structure-edit-payout').live('click', function() {
        ppObj.method = 'update';
        ppObj.setPayoutPercentList();
        $payoutDialog.html(ppObj.getForm());
        $payoutDialog.dialog('open');
        return false;
    });
    
    $('button[name=rep-structure-update-payout]').live('click', function() {
        var index = $(this).val();
        var params = {};
        params.repNum = $('select[name=brokerNumber]').val();
        params.minAmt = $('#rep-structure-update-table tr').find('input[name=minAmt]').get(index).value;
        params.maxAmt = $('#rep-structure-update-table tr').find('input[name=maxAmt]').get(index).value;
        params.payPercent = ($('#rep-structure-update-table tr').find('input[name=payPercent]').get(index).value / 100);
        if (ppObj.isValidPercent(params.payPercent)) {
            var dialog = ppObj.simpleModalObject('Submission Error',null);
            dialog.html('Please enter a valid percent.');
            dialog.dialog('open');
            return false;
        }
        console.dir(params);
        if (ppObj.payoutPercentList[index].id != null) {
            params.id = ppObj.payoutPercentList[index].id;
            dbw.table  = 'payoutStructure';
            dbw.updateObject(params, function(data) {
                console.dir(data);
            });
        } else {
            dbw.table  = 'payoutStructure';
            dbw.insertObject(params, function(data) {
                console.dir(data);
            });
        }
        ppObj.setPayoutPercentList();
        $payoutDialog.html(ppObj.getForm());
        $payoutDialog.dialog('open');
        return false;
    });
    
    $('button[name=rep-structure-delete-payout]').live('click', function() {
        var index = $(this).val();
        if (ppObj.payoutPercentList[index].id != null) {
            dbw.table  = 'payoutStructure';
            dbw.deleteObject(ppObj.payoutPercentList[index].id, function(data) {
                console.dir(data);
            });
            ppObj.setPayoutPercentList();
            $payoutDialog.html(ppObj.getForm());
            $payoutDialog.dialog('open');
            return false;
        }
    });
    
    $('button[name=rep-structure-add-payout]').live('click', function() {
        var index = $(this).val();
        var emptyPayout = {};
        emptyPayout.id = null;
        emptyPayout.repNum = '';
        emptyPayout.minAmt = '';
        emptyPayout.maxAmt = '';
        emptyPayout.payPercent = '';
        ppObj.payoutPercentList.push(emptyPayout);
        $payoutDialog.html(ppObj.getForm());
        $payoutDialog.dialog('open');
        return false;
    });
    
});