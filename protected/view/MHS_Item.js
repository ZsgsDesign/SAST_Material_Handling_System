/*
* 这里放置一些通用的物品操作代码
*/
function add(i) {
    var count = $('#count' + i).text();
    $('#count' + i).text(++count);
    $('#minus' + i).attr('disabled',false);
}
function minus(i) {
    var count = $('#count' + i).text();
    if(count>1)
        $('#count' + i).text(--count);
    if(count<=1){
        $('#minus' + i).attr('disabled',true); //防止减到1以下
    }
}
function showResult(result) {
    console.log(result);
    result = JSON.parse(result);
    console.log(result);
    if(result.ret=200) //成功
        $.snackbar({content: result.desc,style:"toast text-center atsast-toast"});
    else
        $.snackbar({content: result.desc,style:"toast text-center atsast-toast"});
    return result.ret;
}
function showDialog(text,title,func) {
    var dialog = "<div class=\"modal fade\" id=\"Confirm\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"Confirm\" aria-hidden=\"true\">\n" +
        "    <div class=\"modal-dialog modal-dialog-centered\" role=\"document\">\n" +
        "        <div class=\"modal-content\">\n" +
        "            <div class=\"modal-header\">\n" +
        "                <h5 class=\"modal-title\">" + title + "</h5>\n" +
        "            </div>\n" +
        "            <div class=\"modal-body\">\n" +
        "                <p>" + text +"</p>\n" +
        "            </div>\n" +
        "            <div class=\"modal-footer\">\n" +
        "                <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">取消</button>\n" +
        "                <button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\" onclick=\"" + func + ";\">确定</button>\n" +
        "            </div>\n" +
        "        </div>\n" +
        "    </div>\n" +
        "</div>";
    var dom = $(dialog);
    dom.modal('show');
}
function addToCart(id,i) {
    $.post("<{$MHS_DOMAIN}>/ajax/AddToCart",{
        iid:id,
        count:$('#count' + i).text()
    },(result) => showResult(result));
}
function removeItem(id,name) {
    $.post("<{$MHS_DOMAIN}>/ajax/RemoveItem",{
        iid:id
    },(result) => showResult(result));
    setTimeout(function(){
        window.location.reload();
    },1000); // 跳转前等待
}
