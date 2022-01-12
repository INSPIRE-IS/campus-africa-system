<!DOCTYPE html>
    <body style="font-family: arial,sans-serif; margin: 0">
        <table width="100%">
            <tr width="100%">
                <td style="padding: 30px 6% 30px 6%; margin: 0;">
                    <img src='<?php echo "https://$_SERVER[HTTP_HOST]"; ?>/images/logo/Campus_Africa_Logo.png' alt="Logo">
                </td>
            </tr>
            <tr width="100%" style="background-color: #f1cc3e;">
                <td colspan="2" style="width: 100%; padding: 30px 6% 30px 6%;; line-height: 22px">   
                
                    <p style="color: #464749; font-size: 21px; font-weight: 400; line-height: 30px;">User registered</p>
                    <br/>
                    <p style="color: #464749; font-size: 21px; font-weight: 400; line-height: 30px;">Hi {{$name}}, we’ve registered you account.</p>

                    <p style="color: #464749; font-size: 21px; font-weight: 400; line-height: 30px;">Please go to: <a href="<?php echo "https://$_SERVER[HTTP_HOST]"; ?>/login"><?php echo "https://$_SERVER[HTTP_HOST]"; ?>/login</a></p>
                    <p style="color: #464749; font-size: 21px; font-weight: 400; line-height: 30px;">Login with:
                        <br/><b>Email:</b> {{$email}}
                        <br/><b>Password:</b> {{$password}}
                    </p>

                    <p style="color: #464749; font-size: 21px; font-weight: 400; line-height: 30px;">The Campus Africa Team</p>
                    
                    <h3 style="color: #464749; font-size: 15px; font-weight: 400; line-height: 20px;">If you have any questions, please reply to this email or contact us at <br/><a href="mailto:hello@campusafrica.co.za" style="color:inherit; text-decoration:none">hello@campusafrica.co.za</a></h3>
                </td>
            </tr>
        </table>
        </div>
    </body>