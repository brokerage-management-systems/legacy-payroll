function DbWrapper(){this.table=""}DbWrapper.prototype.deleteObject=function(a,b){var c={};c.id=a;c.method="delete";c.table=this.table;$.ajax({async:!1,type:"POST",url:"/services/driver.php",dataType:"json",data:c,success:b})};DbWrapper.prototype.executeQuery=function(a,b){a==null&&(a={});$.ajax({async:!1,type:"POST",url:"/services/driver.php",dataType:"json",data:a,success:b})};
DbWrapper.prototype.get=function(a,b){var c={};c.id=a;c.method="get";c.table=this.table;$.ajax({async:!1,type:"POST",url:"/services/driver.php",dataType:"json",data:c,success:b})};DbWrapper.prototype.getAll=function(a,b){a==null&&(a={});a.method="getAll";a.table=this.table;$.ajax({async:!1,type:"POST",url:"/services/driver.php",dataType:"json",data:a,success:b})};
DbWrapper.prototype.getByFields=function(a,b){a.method="getByFields";a.table=this.table;$.ajax({async:!1,type:"POST",url:"/services/driver.php",dataType:"json",data:a,success:b})};DbWrapper.prototype.getByJsonSQL=function(a,b){a.method="getByJsonSQL";a.table=this.table;$.ajax({async:!1,type:"POST",url:"/services/driver.php",dataType:"json",data:a,success:b})};DbWrapper.prototype.getByRawSQL=function(){};
DbWrapper.prototype.insertObject=function(a,b){a.method="insert";a.table=this.table;$.ajax({async:!1,type:"POST",url:"/services/driver.php",dataType:"json",data:a,success:b})};DbWrapper.prototype.updateObject=function(a,b){a.method="update";a.table=this.table;$.ajax({async:!1,type:"POST",url:"/services/driver.php",dataType:"json",data:a,success:b})};DbWrapper.prototype.stringifyAndEncodeJson=function(){var a=Base64.encode(JSON.stringify(jsonSQL)),b=Base64.decode(a);alert(a);alert(b);return a};
var dbw=null;