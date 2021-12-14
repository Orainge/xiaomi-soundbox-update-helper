var username = "";

var HARDWARE = "";
var DEVICEID = "";
var SN = "";

var firmwares = [];
var currFirmware = undefined;
var currChoice = undefined;

var nodeSize = 0;
var currNodeIndex = 0;

$(document).ready(() => {
    getFirmwares();
    $('#pushBtn').click(function (e) {
        if (username != "" && DEVICEID != "" && SN != "" && currFirmware != undefined && currChoice != undefined) {
            push(currFirmware.name, currFirmware.hardware, currFirmware.type, currFirmware.version, currFirmware.link, currFirmware.hash, currFirmware.extra, currFirmware.batch);
        } else {
            $('#TipsModalLabel').text('推送提示');
            $('#tipsModal .modal-footer .TipsCloseBtn').css({
                'display': 'inline-block'
            });
            $('#tipsModal .modal-body').text('至少有一个选项未选择，请完成前面的步骤再推送！');
            $('#tipsModal').modal('show');
        }
    });

    $('#tipsModal').on('hide.bs.modal', function () {
        $('#tipsModal .modal-footer .TipsCloseBtn').css({
            'display': 'none'
        });
        $('#tipsModal .modal-footer .tipsOkBtn').css({
            'display': 'none'
        });
        $('#tipsModal .modal-footer .pushNextBtn').css({
            'display': 'none'
        });
    });

    // 显示免责声明
    showDisclaimer();
});

function showDisclaimer() {
    // 免责声明
    $('#disclaimerModal').modal()
}

function login() {
    username = $('#username').val();
    let password = $('#password').val();

    var passwordMd5 = $.md5(password).toUpperCase();

    if (username == "" || password == "") {
        $('#TipsModalLabel').text('登录提示');
        $('#tipsModal .modal-footer .TipsCloseBtn').css({
            'display': 'inline-block'
        });
        $('#tipsModal .modal-body').text('信息不能为空，请检查后重试！');
        $('#tipsModal').modal('show');
        return;
    }

    console.log('Username: ' + username);
    console.log('Password (MD5):' + passwordMd5);

    let loginbtn = $('#loginBtn');
    loginbtn.text('正在登录');
    loginbtn.attr({
        "disabled": "disabled"
    });

    post_url = './php/main.php?action=login&username=' + username + '&password=' + passwordMd5;
    $.ajax({
        url: post_url,
        success: function (data) {

            data = data.replace("&&&START&&&", "")
            console.log(data)
            console.log('!!! Start loading device information !!!');

            /*
            // 此处视环境而定，如果返回值为 '[]' 则需要判空

            if(data == '[]'){
                console.log('!!! Damn it! The return value is not in JSON format !!!');
                console.log('!!! Login failed! Check if the information is wrong !!!');
                
                $('#TipsModalLabel').text('登录错误 (错误码: callbakJsonError)');
                $('#tipsModal .modal-footer .TipsCloseBtn').css({'display': 'inline-block'});
                $('#tipsModal .modal-body').text('登录失败，请核对密码与账号输入是否正确!');
                $('#tipsModal').modal('show');

                loginbtn.text('登录');
                loginbtn.removeAttr("disabled");

                return;
            }
            */
            if (isJSON(data) != true) {
                console.log('!!! Damn it! The return value is not in JSON format !!!');
                console.log('!!! Login failed! Check if the information is wrong !!!');

                $('#TipsModalLabel').text('登录错误 (错误码: formatError)');
                $('#tipsModal .modal-footer .TipsCloseBtn').css({
                    'display': 'inline-block'
                });
                $('#tipsModal .modal-body').text('登录失败，请核对密码与账号输入是否正确!');
                $('#tipsModal').modal('show');

                loginbtn.text('登录');
                loginbtn.removeAttr("disabled");

                return;
            }

            console.log('DeviceList: ' + data);

            var arr = JSON.parse(data);
            console.log(arr);
            let state = $('#state');
            if (Array.isArray(arr)) {
                getDevices(arr);
                loginbtn.text('已登录');
                $('.loginLabel').css({
                    'display': 'inline-block'
                });
                return;
            }
            loginbtn.text('登录');
            loginbtn.removeAttr("disabled");
        }
    });
}

function isJSON(str) {
    if (typeof str == 'string') {
        try {
            JSON.parse(str);
            return true;
        } catch (e) {
            return false;
        }
    }
}

function getDevices(arr) {
    var deviceTable = $('#deviceTable tbody');
    deviceTable.html('');

    let devices = arr;
    for (let i = 0; i < devices.length; i++) {
        let deviceItem = $(`
            <tr>
                <td>` + devices[i].name + `</td>
                <td>` + devices[i].serialNumber + `</td>
                <td>` + devices[i].hardware + `</td>
                <td><button hardware="` + devices[i].hardware + `" deviceid="` + devices[i].deviceID + `" sn="` + devices[i].serialNumber + `" class="choiceBtn btn btn-default">选择</button></td>
                </tr>
            `);
        deviceTable.append(deviceItem);
    }

    $('.choiceBtn').click((e) => {
        let choiceBtn = $(e.target);

        if (currChoice != undefined) {
            currChoice.attr('class', 'choiceBtn btn btn-default');
            currChoice.text('选择');
        }
        choiceBtn.attr('class', 'choiceBtn btn btn-primary');
        choiceBtn.text('选中');
        currChoice = choiceBtn;

        deviceChoice(choiceBtn.attr('deviceid'), choiceBtn.attr('sn'), choiceBtn.attr('hardware'));
    });
}

function deviceChoice(deviceId, sn, hardware) {
    DEVICEID = deviceId;
    SN = sn;
    HARDWARE = hardware;
}

function getFirmwares() {
    var firmwareTable = $('#firmwareTable tbody');
    firmwareTable.html('');

    $.ajax({
        url: './data/firmwares.json',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            console.log(data);
            firmwares = data;
            console.log(firmwares);
            for (let i = 0; i < firmwares.length; i++) {
                let firmwareItem = $(`
                    <tr>
                        <td>` + firmwares[i].name + `</td>
                        <td>` + firmwares[i].type + `</td>
                        <td>` + firmwares[i].hardware + `</td>
                        <td>` + firmwares[i].version + `</td>
                        <td><button index="` + i + `" class="firmwareBtn btn btn-default">选择</button></td>
                    </tr>
                `);
                firmwareTable.append(firmwareItem);
            }

            var currfirmware = undefined;
            $('.firmwareBtn').click((e) => {
                let firmwareBtn = $(e.target);
                if (currfirmware != undefined) {
                    currfirmware.attr('class', 'firmwareBtn btn btn-default');
                    currfirmware.text('选择');
                }
                firmwareBtn.attr('class', 'firmwareBtn btn btn-primary');
                firmwareBtn.text('选中');
                currfirmware = firmwareBtn;

                firmwareChoice(firmwareBtn.attr('index'));
            });
        }
    });
}

function firmwareChoice(index) {
    currFirmware = firmwares[index];
    let pushLabel = $('.pushLabel');
    if (currFirmware.batch) {
        pushLabel.css({
            'display': 'inline-block'
        });
    } else {
        pushLabel.css({
            'display': 'none'
        });
    }
}

function getStr(str, start, end) {
    let res = str.match(new RegExp(`${start}(.*?)${end}`))
    return res ? res[1] : null
}

function push(name, hardware, type, version, link, hash, extra, batch) {
    let pushBtn = $('#pushBtn');
    pushBtn.text('请稍等');
    pushBtn.attr({
        "disabled": "disabled"
    });

    if (hardware != HARDWARE) {
        $('#TipsModalLabel').text('推送错误 (错误码: deviceMismatch)');
        $('#tipsModal .modal-footer .TipsCloseBtn').css({
            'display': 'inline-block'
        });
        $('#tipsModal .modal-body').text('已选择的固件只能用于设备 ' + hardware + " 而已选择的设备是 " + HARDWARE);
        $('#tipsModal').modal('show');
        pushBtn.text('开始');
        pushBtn.removeAttr("disabled");
        return;
    } //如果机型不符


    post_url = './php/main.php?action=push';

    $.ajax({
        url: post_url,
        type: 'GET',
        data: {
            username,
            name,
            hardware,
            type,
            version,
            link,
            hash,
            extra,
            batch,
            deviceId: DEVICEID,
            sn: SN
        },
        success: (data) => {
            console.log('!!! Success !!!');

            var stateCode = getStr(data, '{"code":', ',');
            console.log('State: ' + stateCode);

            if (stateCode == '0') {
                $('#TipsModalLabel').text('推送提示');
                $('#tipsModal .modal-footer .TipsCloseBtn').css({
                    'display': 'inline-block'
                });
                $('#tipsModal .modal-body').text('推送成功，请检查是否成功推送！');
                $('#tipsModal').modal('show');
                pushBtn.text('开始');
                pushBtn.removeAttr("disabled");
            } else if (stateCode == '-1') {
                $('#TipsModalLabel').text('推送错误 (错误码: deviceOffline)');
                $('#tipsModal .modal-footer .TipsCloseBtn').css({
                    'display': 'inline-block'
                });
                $('#tipsModal .modal-body').text('请检查设备是否联网，或者重启设备后重试！');
                $('#tipsModal').modal('show');
                pushBtn.text('开始');
                pushBtn.removeAttr("disabled");
            } else {
                console.log(data);
                $('#TipsModalLabel').text('推送错误 (错误码: Unkown)');
                $('#tipsModal .modal-footer .TipsCloseBtn').css({
                    'display': 'inline-block'
                });
                $('#tipsModal .modal-body').text('出现了预料之外的错误，已经输出详细信息在控制台内');
                $('#tipsModal').modal('show');
                pushBtn.text('开始');
                pushBtn.removeAttr("disabled");
            }
        }
    });
}