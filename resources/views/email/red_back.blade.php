<!DOCTYPE html>
    <body style="font-family: arial,sans-serif; margin: 0">
        <table width="100%">
            <tr width="100%">
                <td style="padding: 30px 6% 30px 6%; margin: 0;">
                    <img src='<?php echo "https://$_SERVER[HTTP_HOST]"; ?>/images/logo/Campus_Africa_Logo.png' alt="Logo">
                </td>
            </tr>
            <tr width="100%" style="background-color: #e74f26;">
                <td colspan="2" style="width: 100%; padding: 30px 6% 30px 6%; line-height: 22px">   
                    {!!$messageForMail!!}
                </td>
            </tr>
        </table>
        </div>
    </body>