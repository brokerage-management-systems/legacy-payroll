$(document).ready(function(){
    dbw = new DbWrapper();
    var params = {"method":"executeQuery","table":"Brokers","select":["repNumber","firstName","lastName","active"],"where":["active=1","firstName!='JointRep'"],"order by":["lastName", "repNumber"],"limit":[0,10]};
    //var params = {"method":"executeQuery","table":"Brokers","select":["repNumber","firstName","lastName","active"],"where":["active=1"]};
    //var params = {"method":"executeQuery","table":"Brokers","select":["repNumber","firstName","lastName","active"],"where":["active=1"]};
    //var params = {"method":"executeQuery","table":"Brokers","select":["repNumber","firstName","lastName","active"],"where":["active=1"]};
    dbw.executeQuery(params, function(data) {
        console.dir(data);
    });
});