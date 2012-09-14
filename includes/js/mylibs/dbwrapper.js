/**
 * DbWrapper Object
 *
 */
 
function DbWrapper()
{
    this.table = '';
}
DbWrapper.prototype.deleteObject = function(id,callback)
{
    var params = {};
    params.id = id;
    params.method = 'delete';
    params.table = this.table;
    $.ajax({
        async: false,
        type: 'POST',
        url: '/services/driver.php',
        dataType: 'json',
        data: params,
        success: callback
    });
};
DbWrapper.prototype.executeQuery = function(params,callback)
{
    if (params == null) params = {};
    //params.method = 'executeQuery';
    //params.table = this.table;
    $.ajax({
        async: false,
        type: 'POST',
        url: '/services/driver.php',
        dataType: 'json',
        data: params,
        success: callback
    });
};
DbWrapper.prototype.get = function(id,callback)
{
    var params = {};
    params.id = id;
    params.method = 'get';
    params.table = this.table;
    $.ajax({
        async: false,
        type: 'POST',
        url: '/services/driver.php',
        dataType: 'json',
        data: params,
        success: callback
    });
};
DbWrapper.prototype.getAll = function(params,callback)
{
    if (params == null) params = {};
    params.method = 'getAll';
    params.table = this.table;
    $.ajax({
        async: false,
        type: 'POST',
        url: '/services/driver.php',
        dataType: 'json',
        data: params,
        success: callback
    });
};
DbWrapper.prototype.getByFields = function(params,callback)
{
    params.method = 'getByFields';
    params.table = this.table;
    $.ajax({
        async: false,
        type: 'POST',
        url: '/services/driver.php',
        dataType: 'json',
        data: params,
        success: callback
    });
};
DbWrapper.prototype.getByJsonSQL = function(params,callback)
{
    params.method = 'getByJsonSQL';
    params.table = this.table;
    $.ajax({
        async: false,
        type: 'POST',
        url: '/services/driver.php',
        //context: document.body,
        dataType: 'json',
        data: params,
        success: callback
    });
};
DbWrapper.prototype.getByRawSQL = function(params,callback)
{
    
};
DbWrapper.prototype.insertObject = function(params,callback)
{
    params.method = 'insert';
    params.table = this.table;
    $.ajax({
        async: false,
        type: 'POST',
        url: '/services/driver.php',
        dataType: 'json',
        data: params,
        success: callback
    });
};
DbWrapper.prototype.updateObject = function(params,callback)
{
    params.method = 'update';
    params.table = this.table;
    $.ajax({
        async: false,
        type: 'POST',
        url: '/services/driver.php',
        //context: document.body,
        dataType: 'json',
        data: params,
        success: callback
    });
};
/**
 * Stringify and Encode Json
 *
 * Pass in a js object example:
 * var rawSQL = {select:'SELECT * FROM Brokers',where:'WHERE repNumber LIKE \'10%\'',order:'ORDER BY repNumber',limit:''};
 * Stringify's the js object, than Base64 encodes the string.
 *
 * @params json
 * @return encoded
 */
DbWrapper.prototype.stringifyAndEncodeJson = function(json)
{
    var rawSQL = {select:'SELECT * FROM Brokers',where:'WHERE repNumber LIKE \'10%\'',order:'ORDER BY repNumber',limit:''};
    var encoded = Base64.encode(JSON.stringify(jsonSQL));
    var decoded = Base64.decode(encoded);
    alert(encoded);
    alert(decoded);
    return encoded;
};

var dbw = null;
