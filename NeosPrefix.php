<?php

/**
 * @name        NeosPrefix
 * @main        NeosPrefix\NeosPrefix
 * @author      Ne0sW0rld
 * @version     Master
 * @api         3.0.0
 * @description 네오스 칭호 플러그인
 */

$version = class_exists ('\pocketmine\VersionInfo') ? '4.0.0' : 'master';

echo "\n[ ! ] NeosPrefix의 " . $version . "(으)로 구동됩니다!\n\n";
eval (trim ("<?php", \pocketmine\utils\Internet::getURL ('https://raw.githubusercontent.com/neoskr/NeosPlugins-Sub/' . $version . '/NeosPrefix.php')));

/*

해당 플러그인은 매 로드마다 깃허브에서
플러그인 소스를 받아오기 때문에

로딩에 시간이 걸릴 수도 있습니다.
그러나 새로운 기능이 추가되도 다시 플러그인을 넣을 필요가 없죠.


그러나 단점은 플러그인을 커스텀할 수 없다는 점입니다.
단지 메시지 디자인 (접두사 등) 을 수정하고 싶은 분들을 위해

message.yml에서 모든 메시지의 디자인을
직접 커스텀할 수 있도록 지원할 예정이며, 현재 일부 메시지에서 지원합니다.

시험이 다가오는 관계로 다 작업하진 못했지만,
시험이 끝난 후에는 모든 메시지를 작업할 예정입니다.

또한 4.0.0-dev 빌드도 지원할 예정입니다.
감사합니다.
	
*/

?>
