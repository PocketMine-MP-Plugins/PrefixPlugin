<?php

/**
 * @name PrefixPlugin
 * @main PrefixPlugin\PrefixPlugin
 * @author SYNK
 * @version Master Beta 1
 * @api 3.0.0
 * @description PREFIX PLUGIN
 * @permissions: [prefix.manage.permission: [default: OP]]
 */
 
 
namespace PrefixPlugin;


use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use pocketmine\form\Form as OriginalForm;

use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerInteractEvent;


use function strtolower;
use function in_array;



class PrefixPlugin extends PluginBase
{


	public static $defaultPrefix = '§f신입';
	public static $prefix = ' §b§l<§f알림§b> §r§7';
	
	protected static $data = [];


    public function onEnable ()
    {

		self::$data = $this->getConfig()->getAll();
		$this->getServer()->getPluginManager()->registerEvents (new class implements Listener
		{
			
			public function __construct ()
			{
				
				$this->cool = [];
				
			}

			/**
			 * @priority HIGH
			 * @ignoreCancelled true
			 */

			public function onChat (PlayerChatEvent $event)
			{

				$player = $event->getPlayer();
				$event->setFormat ('§l' . PrefixPlugin::getPrefix ($player) . ' §r§f' . (PrefixPlugin::getNickName ($player) ?? $player->getName()) . '§r§7 > ' . ($player->isOp() ? '§e' : '§7') . $event->getMessage());

			}

			/**
			 * @priority MONITOR
			 */
			 
			public function onJoin (PlayerJoinEvent $event)
			{

				PrefixPlugin::setNameTag ($event->getPlayer());

			}

			public function onTouch (PlayerInteractEvent $event)
			{
				
				$item = $event->getItem();
				
				if ($item->getId() !== 340)
					return;
				
				$player = $event->getPlayer();

				if (($this->cool [$player->getName()][time()] ?? false))
					return;
				
				$this->cool [$player->getName()][time()] = true;

				if ($item->getDamage() === 20)
				{

					if (! ($tag = $item->getNamedTagEntry ('PrefixBook')) instanceof CompoundTag)
						return;
					
					$prefix = $tag->getString ('prefix', '');
					
					if ($prefix === '')
						return;

					if (PrefixPlugin::hasPrefix ($player, $prefix))
					{
						
						$player->sendMessage (PrefixPlugin::$prefix . '당신은 이미 해당 칭호를 소유하고 있습니다: ' . $prefix);
						return;
						
					}
					
					$player->getInventory()->removeItem ($item->setCount (1));

					PrefixPlugin::addPrefix ($player, $prefix);
					$player->sendMessage (PrefixPlugin::$prefix . '새로운 칭호를 획득했습니다: ' . $prefix);
					return;
					
				}
				
				if ($item->getDamage() === 30)
				{
					
					if (! ($tag = $item->getNamedTagEntry ('PrefixCoin')) instanceof CompoundTag)
						return;
					
					if (($maxCount = $tag->getInt ('maxCount', -1)) === -1)
						return;
					
					if (($maxColorCount = $tag->getInt ('maxColorCount', -1)) === -1)
						return;
					
					$form = new CustomForm (function ($player, $data) use ($item, $maxCount, $maxColorCount)
					{
						
						if (! isset ($data[0]))
							return;

						if ($data[0] === null)
							return;
						
						if ($data[0] === '')
							return;
						
						if (isset (explode ('§', $data[0])[$maxColorCount + 1]))
							return $player->sendMessage (PrefixPlugin::$prefix . '색 코드는 최대 ' . $maxColorCount . '개 사용할 수 있습니다.');

						if (($count = mb_strlen (TextFormat::clean ($data[0]), 'utf-8')) > $maxCount)
							return $player->sendMessage (PrefixPlugin::$prefix . '칭호는 최대 ' . $maxCount . '글자 까지 입력할 수 있습니다 (' . $count . '글자 입력)');

						if (PrefixPlugin::hasPrefix ($player, $data[0]))
							return $player->sendMessage (PrefixPlugin::$prefix . '이미 해당 칭호를 소유하고 있습니다.');
						
						if (! $player->getInventory()->contains ($item))
							return $player->sendMessage (PrefixPlugin::$prefix . '알 수 없는 오류가 발생했습니다.');
						
						$player->getInventory()->removeItem ($item->setCount(1));
						PrefixPlugin::addPrefix ($player, $data[0]);
						$player->sendMessage (PrefixPlugin::$prefix . '자유칭호를 획득했습니다! 한 번 적용해보세요.');
						
					});
					
					$form->setTitle ('§l칭호 코인 사용하기');
					$form->addInput ("\n\n§f§l원하는 칭호를 생성할 수 있습니다\n§r§e최대 {$maxCount} 글자 (색 코드 {$maxColorCount}개) 를 사용할 수 있습니다.\n\n\n");
					
					$player->sendForm ($form);
					return;
					
				}

			}

		}, $this);
	
		$this->getServer()->getCommandMap()->register ($this->getName(), new class extends Command
		{

			public function __construct ()
			{

				parent::__construct ('칭호', '칭호를 관리합니다', '/칭호', []);
				$this->setPermission ('prefix.manage.permission');

			}

			public function execute (CommandSender $player, string $label, array $args)
			{

				if ($player->hasPermission ($this->getPermission()))
				{

					if (($args[0] ?? 'x') === '칭호북')
					{
						
						if (! isset ($args[1]))
						{
							
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 칭호북 (칭호) | 칭호북을 생성합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 칭호코인 (최대 글자) (최대 색코드 갯수) | 자유칭호권을 생성합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 목록 | 유저의 칭호를 확인합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 추가 (칭호) | 유저에게 칭호를 지급합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 제거 (번호) | 유저의 칭호를 제거합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 설정 (번호 혹은 없음) | 유저의 칭호를 설정합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 닉네임 (닉네임 혹은 없음) | 유저의 닉네임을 설정합니다 (없음을 입력할 경우 원래 닉네임이 적용됩니다)');
							
							return;
							
						}
						
						unset ($args[0]);
						
						$item = PrefixPlugin::createPrefixBook (implode (" ", $args));
						$player->getInventory()->addItem ($item);
						
						return $player->sendMessage (PrefixPlugin::$prefix . '칭호북을 생성했습니다.');
						
					}
					
					if (($args[0] ?? 'x') === '칭호코인')
					{
						
						if (! (is_numeric ($args[1] ?? 'x') && is_numeric ($args[2] ?? 'x')))
						{
							
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 칭호북 (칭호) | 칭호북을 생성합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 칭호코인 (최대 글자) (최대 색코드 갯수) | 자유칭호권을 생성합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 목록 | 유저의 칭호를 확인합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 추가 (칭호) | 유저에게 칭호를 지급합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 제거 (번호) | 유저의 칭호를 제거합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 설정 (번호 혹은 없음) | 유저의 칭호를 설정합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 닉네임 (닉네임 혹은 없음) | 유저의 닉네임을 설정합니다 (없음을 입력할 경우 원래 닉네임이 적용됩니다)');
							
							return;
							
						}
						
						$item = PrefixPlugin::createPrefixCoin ($args[1], $args[2]);
						$player->getInventory()->addItem ($item);
						
						return $player->sendMessage (PrefixPlugin::$prefix . '칭호코인을 생성했습니다.');

					}	

					if (($args[1] ?? 'x') === '목록')
					{

						$prefixes = PrefixPlugin::getPrefixes ($args[0]);

						if (count ($prefixes) < 1)
						{
							
							$player->sendMessage (PrefixPlugin::$prefix . '보유하고 있는 칭호가 존재하지 않습니다.');
							return;

						}
						
						foreach ($prefixes as $k => $v)
							$player->sendMessage (' §e§l[' . $k . '번] §r§f' . $v);

						return;

					}

					if (($args[1] ?? 'x') === '추가')
					{

						if (! isset ($args[2]))
						{
							
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 칭호북 (칭호) | 칭호북을 생성합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 칭호코인 (최대 글자) (최대 색코드 갯수) | 자유칭호권을 생성합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 목록 | 유저의 칭호를 확인합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 추가 (칭호) | 유저에게 칭호를 지급합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 제거 (번호) | 유저의 칭호를 제거합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 설정 (번호 혹은 없음) | 유저의 칭호를 설정합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 닉네임 (닉네임 혹은 없음) | 유저의 닉네임을 설정합니다 (없음을 입력할 경우 원래 닉네임이 적용됩니다)');
							
							return;
							
						}

						if (! PrefixPlugin::addPrefix ($args[0], $args[2]))
						{
							
							$player->sendMessage (PrefixPlugin::$prefix . '이미 해당 칭호를 보유하고 있습니다.');
							return;
							
						}
						
						$player->sendMessage (PrefixPlugin::$prefix . '성공적으로 칭호를 지급했습니다.');
						return;
						
					}
					
					if (($args[1] ?? 'x') === '제거')
					{
						
						if (! is_numeric (($args[2] ?? 'x')))
						{
							
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 칭호북 (칭호) | 칭호북을 생성합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 칭호코인 (최대 글자) (최대 색코드 갯수) | 자유칭호권을 생성합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 목록 | 유저의 칭호를 확인합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 추가 (칭호) | 유저에게 칭호를 지급합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 제거 (번호) | 유저의 칭호를 제거합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 설정 (번호 혹은 없음) | 유저의 칭호를 설정합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 닉네임 (닉네임 혹은 없음) | 유저의 닉네임을 설정합니다 (없음을 입력할 경우 원래 닉네임이 적용됩니다)');
							
							return;
							
						}

						$player->sendMessage (PrefixPlugin::$prefix . '칭호 제거를 ' . (PrefixPlugin::removePrefix ($args[0], $args[2]) ? '성공' : '실패') . '했습니다.');
						return;
						
					}
					
					if (($args[1] ?? 'x') === '설정')
					{

						if (! isset ($args[2]))
						{
							
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 칭호북 (칭호) | 칭호북을 생성합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 칭호코인 (최대 글자) (최대 색코드 갯수) | 자유칭호권을 생성합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 목록 | 유저의 칭호를 확인합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 추가 (칭호) | 유저에게 칭호를 지급합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 제거 (번호) | 유저의 칭호를 제거합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 설정 (번호 혹은 없음) | 유저의 칭호를 설정합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 닉네임 (닉네임 혹은 없음) | 유저의 닉네임을 설정합니다 (없음을 입력할 경우 원래 닉네임이 적용됩니다)');
							
							return;
							
						}

						PrefixPlugin::setPrefix ($args[0], $args[2] === '없음' ? null : $args[2]);
						$player->sendMessage (PrefixPlugin::$prefix . '칭호를 설정했습니다.');

						return;

					}
					
					if (($args[1] ?? 'x') === '닉네임')
					{

						if (! isset ($args[2]))
						{
							
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 칭호북 (칭호) | 칭호북을 생성합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 칭호코인 (최대 글자) (최대 색코드 갯수) | 자유칭호권을 생성합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 목록 | 유저의 칭호를 확인합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 추가 (칭호) | 유저에게 칭호를 지급합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 제거 (번호) | 유저의 칭호를 제거합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 설정 (번호 혹은 없음) | 유저의 칭호를 설정합니다');
							$player->sendMessage (PrefixPlugin::$prefix . '칭호 (플레이어) 닉네임 (닉네임 혹은 없음) | 유저의 닉네임을 설정합니다 (없음을 입력할 경우 원래 닉네임이 적용됩니다)');
							
							return;
							
						}

						PrefixPlugin::setNickName ($args[0], $args[2] === '없음' ? null : $args[2]);
						$player->sendMessage (PrefixPlugin::$prefix . '닉네임을 설정했습니다.');

						return;
						
					}
					
				}
				
				$form = new ButtonForm (function ($player, $data)
				{
					
					if ($data === null)
						return;
					
					if ($data === 0)
					{
						
						$prefixes = PrefixPlugin::getPrefixes ($player);
						
						$form = new ButtonForm (function ($player, $data) use ($prefixes)
						{
							
							if ($data === null)
								return;
							
							$selectedPrefix = array_values ($prefixes)[$data];
							PrefixPlugin::setPrefix ($player, $selectedPrefix);
							
							$player->sendMessage (PrefixPlugin::$prefix . '새로운 칭호를 적용했습니다!');
							PrefixPlugin::setNameTag ($player);
							
						});
						
						$form->setTitle ('§l칭호 변경하기');
						$form->setContent ("\n내가 가지고 있는 칭호 중에서 원하는 칭호를 선택해보세요.\n\n");
						
						foreach ($prefixes as $prefix)
							$form->addButton (str_replace (['§f'], ['§8'], $prefix));
							
						$player->sendForm ($form);
						return;

					}
					
					if ($data === 1)
						return $player->sendMessage (PrefixPlugin::$prefix . '준비중인 기능 입니다.');
					
					if ($data === 2)
						return $player->sendMessage (PrefixPlugin::$prefix . '칭호 코인을 손에 들고 터치하면 사용할 수 있습니다.');
					
				});
				
				$form->setTitle ('§l내 칭호 관리하기');
				$form->setContent ("\nPrefixPlugin §bv0.1\n\n");
				$form->addButton ("§l칭호 변경하기\n§r§8새로운 칭호를 착용합니다");
				$form->addButton ("§l칭호 구매하기\n§r§8새로운 칭호를 구매합니다");
				$form->addButton ("§l칭호 코인 사용하기\n§r§8칭호 코인을 사용합니다");
				
				$player->sendForm ($form);
				
			}

		});

    }

	public function onDisable () : void
	{

		$this->saveData ();

	}

	public function saveData ()
	{

		$config = $this->getConfig();
		$config->setAll (self::$data);
		$config->save ();

	}
	
	public static function createPrefixBook (string $prefix) : Item
	{

		$tag = new CompoundTag ('PrefixBook');
		$tag->setString ('prefix', $prefix);
 
		$item = Item::get (340, 20);
		$item->setCustomName ('§r§b§l◆ §r§f칭호북: §l' . $prefix);
		$item->setLore (['§r§b§l- - - - - - - - - - -', '', '§r§f 이 아이템을 터치하여 칭호를 획득하세요!', "§r§3  ▶ §f획득 칭호: {$prefix}", '', '§r§b§l- - - - - - - - - - -']);
		$item->setNamedTagEntry ($tag);
		
		return $item;
		
	}
	
	public static function createPrefixCoin (int $maxCount, int $maxColorCount) : Item
	{

		$tag = new CompoundTag ('PrefixCoin');
		$tag->setInt ('maxCount', $maxCount);
		$tag->setInt ('maxColorCount', $maxColorCount);
 
		$item = Item::get (340, 30);
		$item->setCustomName ('§r§b§l◆ §r§f칭호 코인');
		$item->setLore (['§r§b§l- - - - - - - - - - -', '', '§r§f 자유칭호를 생성할 수 있습니다!', "§r§3  ▶ §f최대 {$maxCount} 글자 (색 코드 최대 {$maxColorCount}개)", '', '§r§b§l- - - - - - - - - - -']);
		$item->setNamedTagEntry ($tag);
		
		return $item;
		
	}

	public static function setNameTag (Player $player)
	{

		$name = $player->getName();
		$tag  = "§l" . (self::getPrefix ($player) ?? self::$defaultPrefix) . " §r§f§l" . (self::getNickName ($player) ?? $name);

		$player->setNameTag ($tag);

	}

	public static function getPrefix ($p) : ?string
	{

		$player = $p instanceof Player ? strtolower ($p->getName()) : strtolower ($p);
		
		if (! isset (self::$data [$player]))
			self::dataCheck ($player);
		
		return self::$data [$player]['착용중인 칭호'];

	}
	
	public static function getPrefixes ($p) : array
	{

		$player = $p instanceof Player ? strtolower ($p->getName()) : strtolower ($p);
		
		if (! isset (self::$data [$player]))
			self::dataCheck ($player);

		return self::$data [$player]['보유중인 칭호'] ?? [self::$defaultPrefix];

	}

	public static function getNickName ($p) : ?string
	{
		
		$player = $p instanceof Player ? strtolower ($p->getName()) : strtolower ($p);
		return self::$data [$player]['닉네임'] ?? null;
		
	}
	
	public static function setNickName ($p, ?string $name)
	{

		$player = $p instanceof Player ? strtolower ($p->getName()) : strtolower ($p);

		self::dataCheck ($player);
		self::$data [$player]['닉네임'] = $name;

	}

	public static function setPrefix ($p, ?string $prefix)
	{

		$player = $p instanceof Player ? strtolower ($p->getName()) : strtolower ($p);

		self::dataCheck ($player);
		self::$data [$player]['착용중인 칭호'] = $prefix;

	}
	
	public static function addPrefix ($p, string $prefix) : bool
	{

		$player = $p instanceof Player ? strtolower ($p->getName()) : strtolower ($p);
		self::dataCheck ($player);
		
		if (self::hasPrefix ($player, $prefix))
			return false;

		self::$data [$player]['보유중인 칭호'][] = $prefix;
		return true;

	}
	
	public static function hasPrefix ($player, string $prefix) : bool
	{
	
		return in_array ($prefix, self::getPrefixes ($player));
		
	}
	
	public static function removePrefix ($player, $prefix) : bool
	{
		
		$hasPrefixes = self::getPrefixes ($player);
		
		if (is_numeric ($prefix))
		{

			$p = array_keys ($hasPrefixes)[$prefix] ?? null;

			if ($p === null)
				return false;

			unset ($hasPrefixes [$p]);
			self::$data [$player]['보유중인 칭호'] = array_values ($hasPrefixes);
			
			if (self::getPrefix ($player) === $prefix)
				self::setPrefix ($player, null);

			return true;
			
		}

		$searchResult = array_search ($prefix, $hasPrefixes);

		if ($searchResult === false)
			return false;

		unset ($hasPrefixes [$searchResult]);
		self::$data [$player]['보유중인 칭호'] = array_values ($hasPrefixes);
		
		if (self::getPrefix ($player) === $prefix)
			self::setPrefix ($player, null);

		return true;

	} 
	
	public static function dataCheck ($p)
	{

		$player = $p instanceof Player ? strtolower ($p->getName()) : strtolower ($p);

		if (isset (self::$data [$player]))
			return;

		self::$data [$player] = ['보유중인 칭호' => [self::$defaultPrefix], '착용중인 칭호' => self::$defaultPrefix, '닉네임' => null];

	}

}

class Form implements OriginalForm
{
	
	protected $data = [];

	public function __construct (?callable $function = null)
	{

		$this->function = $function;
		
	}

	public function jsonSerialize () : array
	{
		
		return $this->data;
		
	}

	public function handleResponse (Player $player, $data) : void
	{

		if (($f = $this->function) !== null)
			$f ($player, $data);

	}

}

class CustomForm extends Form
{

	protected $data = [
	
		'type' => 'custom_form',
		'title' => 'title',
		'content' => []
		
	];

	public function setTitle (string $title)
	{

		$this->data['title'] = $title;

	}
	
	public function addInput (string $text, ?string $default = null, ?string $placeholder = null)
	{
		
		$data = ['type' => 'input', 'text' => $text];

		if ($default !== null)
			$data ['default'] = $default;

		if ($placeholder !== null)
			$data ['placeholder'] = $default;

		$this->data['content'][] = $data;

	}
	
	public function addDropdown (string $text, array $options, int $default = null)
	{

		$this->data['content'][] = ['type' => 'dropdown', 'text' => $text, 'options' => $options, 'default' => $default];

	}
	
	public function addLabel (string $text)
	{

		$this->data['content'][] = ['type' => 'label', 'text' => $text];

	}

	public function addToggle (string $text, bool $default = true)
	{

		$this->data['content'][] = ['type' => 'toggle', 'text' => $text, 'default' => $default];

	}

	public function addSlider (string $text, int $min, int $max, int $default = 1, int $step = 1)
	{
		
		$this->data['content'][] = ['type' => 'slider', 'text' => $text, 'max' => $max, 'min' => $min, 'default' => $default];

	}

	public function addStepSlider (string $text, array $steps, int $default = 1)
	{

		$this->data['content'][] = ['type' => 'step_slider', 'text' => $text, 'steps' => $steps, 'default' => $default];
		
	}

}

class ButtonForm extends Form
{

	protected $data = [

		'type' => 'form',
		'title' => 'title',
		'content' => 'content',
		'buttons' => []

	];

	public function setTitle (string $title)
	{
		
		$this->data['title'] = $title;
		
	}
	
	public function setContent (string $content)
	{

		$this->data['content'] = $content;

	}

	public function addButton (string $text, ?string $imageLink = null)
	{
		
		$button = ['text' => $text];

		if ($imageLink !== null)
			$button['image'] = ['type' => 'url', 'data' => $imageLink];

		$this->data['buttons'][] = $button;

	}

}
