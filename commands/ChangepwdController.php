<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\stat\model\NewPasswords;
use app\stat\model\User;
use app\stat\Mailer;
use app\stat\Configuration;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ChangepwdController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex()
    {
        set_time_limit(0);
        Configuration::loadConfiguration();
        $npList = NewPasswords::getRows();

        foreach ($npList as $val) {
            $user = new User((int) $val['Id']);
            $head = 'Внимание! Сменился пароль для доступа на портал РОССПЕЦМАШ_СТАТ';
            $newPasswd = $val['Password'];
            $user->setNewPassword($newPasswd);
            $user->updateDb();
            $message = $this->getMessageForEmail($newPasswd);
            $mailer = new Mailer();
            $mailer->sendMessage($user->email, $head, $message,true);
            echo "Send mail to: ". $user->email . "\n";
            unset ($user);
            sleep(60);
        }
        
        //echo $message . "\n";
		
		echo 'Bye-'.count($npList);

        return ExitCode::OK;
    }
    protected function getMessageForEmail($password) {
        return '<h2>Уважаемые коллеги!</h2>
                <p>В связи с необходимостью выполнения п.2.2.5 и п.3.1 Соглашения об участии на Интернет-портале "Росспецмаш-стат"
                    о конфиденциальности параметров доступа, а также данных, содержащихся в системе,
                    инициирована <strong>автоматическая смена пароля участника.</strong></p>
                <p><strong>Ваш новый пароль:</strong> <span>'.$password.'</span></p>
                <p>С уважением, администрация портала РОССПЕЦМАШ-СТАТ</p>';                               
    }
}
