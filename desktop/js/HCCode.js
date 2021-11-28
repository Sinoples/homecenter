/*function displayLog(logType, logFile) {
    console.log("displayLog("+logType+", "+logFile+")");
    let action = "";
    if (logType == "JEEDOM-LOG") {
        action = "getFile";
        logPath = "../../log/"+logFile;
    } else {
        action = "getTmpFile";
        logPath = logFile;
    }

    $.ajax({
        type: 'POST',
        url: 'plugins/fibaro/core/ajax/fibaroFile.ajax.php',
        data: {
            action: "getTmpFile",
            file : "fibaro-Decla.log",
        },
        dataType: "json",
        global: false,
        cache: false,
        error: function (request, status, error) {
            alert("File Error");
            //$('#idCurrentDisplay').empty().append('{{Log : }}'+logFile+" => ERREUR");
        },
        success: function (json_res) {
            alert("File Open");            
            
            
            // console.log(json_res);
            res = JSON.parse(json_res.result); // res.status, res.error, res.content
            if (res.status != 0) {
                $('#idCurrentDisplay').empty().append('{{Log : }}'+logFile+" => ERREUR");
            } else {
                var log = res.content;
                $('#idPreResults').empty();
                $('#idCurrentDisplay').empty().append('{{Log : }}'+logFile);
                $('#idPreResults').append(log);
                curDisplay = logFile;
                curDisplayType = logType;
            }
            
        }
    });
}
*/

$('#radio1').off('click').on('click',function() {
    alert("Declaration");

    /*var genKeyInfos = new XMLHttpRequest();
    genKeyInfos.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            displayLog("JEEDOM-TMP", "fibaro-Decla.log")
        }
    };

    genKeyInfos.open("GET", "/plugins/fibaro/core/php/FibaroCS-Decla.php", false);
    genKeyInfos.send();*/
    
});

$('#radio2').off('click').on('click',function() {
    alert("Action");

    /*var genKeyInfos = new XMLHttpRequest();
    genKeyInfos.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            displayLog("JEEDOM-TMP", "AbeilleKeyInfos.log")
        }
    };

    genKeyInfos.open("GET", "/plugins/Abeille/core/php/AbeilleSupportKeyInfos.php", false);
    genKeyInfos.send();
    */
});