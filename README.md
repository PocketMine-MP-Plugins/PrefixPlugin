# NeosPrefix

- A plugin of PocketMine-MP that supports prefix (rank) system on your server
- You *must* apply the EconomyAPI made by Onebone
- I wrote how to use this plugin in English, but please forgive me for my bad English ;d

- 칭호 기능을 지원하는 PocketMine-MP의 플러그인입니다
- 원본님의 EconomyAPI 적용을 필수로 합니다


# Caution

- It doesn't work in PocketMine-MP 4.0.0-dev please use it in 3.X.X


# 주의사항

- 이 플러그인은 PocketMine-MP 4.0.0-dev를 지원하지 않습니다. 3.x.x 빌드를 사용해주세요


# Command

- /칭호 - Open Prefix System UI

- 칭호 설정 <player> <prefix> | Set <player>'s prefix to <prefix>
- 칭호 추가 <player> <prefix> | Add <prefix> in <player>'s prefix slot
- 칭호 제거 <player> <prefix> | Remove <prefix> from <player>'s prefix slot
- 칭호 목록 <player> | Check the list of <player>'s prefix list
- 칭호 한닉 <player> <nickname> | Change <player>'s nickname to <nickname>
- 칭호 티켓 <prefix> | Make prefix (<prefix>) ticket
- 칭호 상점추가 <price> <prefix> | Add <prefix> in prefix shop (the price is <price>)
- 칭호 상점제거 <prefix> | Remove <prefix> from prefix shop
- 칭호 자유칭호권 <name> <maximum number of texts> <maximum number of color code (§)> | Make a new free-prefix-ticket


# 명령어

- /칭호 - 칭호 UI를 엽니다

- 칭호 설정 <유저> <칭호> | 유저의 칭호를 설정합니다
- 칭호 추가 <유저> <칭호> | 유저에게 칭호를 추가합니다
- 칭호 제거 <유저> <칭호> | 유저의 칭호를 제거합니다
- 칭호 목록 <유저> | 유저의 칭호 목록을 확인합니다
- 칭호 한닉 <유저> <닉네임> | 유저의 닉네임을 변경합니다
- 칭호 티켓 <칭호> | 칭호 티켓을 생성합니다
- 칭호 상점추가 <가격> <칭호> | 칭호 상점을 추가합니다
- 칭호 상점제거 <칭호> | 칭호상점을 제거합니다
- 칭호 자유칭호권 <이름> <최대 글자 수> <최대 색코드 수> | 자유칭호권을 이제 명령어로 직접 제작할 수 있습니다


# How to make Sign Prefix Shop

- you can use it where the point of something like contents (like jumpmaps)

- 1st. Place a sign
- 2nd. Type '[칭호상점]' in 1st line.
- 3rd. Type the prefix that you want to make in 2nd line.
- 4th. Type the price that you want to make in 3rd line.

- When you remove the shop, just break the sign
- if you want to buy the prefix from the sign shop, sneak and touch it


# 칭호상점 만드는 방법

- UI 칭호상점에 추가하고 싶지 않은 곳 (이스터에그라던가 컨텐츠 도착 지점에) 에 표지판 상점을 만들 수 있습니다

- 첫번째. 표지판을 설치한다
- 두번째. '[칭호상점]'을 첫째 줄에 적습니다
- 세번째. 칭호와 가격을 각각 2, 3번째 줄에 적습니다

- 부수면 칭호상점에 제거되고 웅크린채로 상점을 터치하면 칭호를 구매하실 수 있습니다


# Changelog [Master 1.0.1]

- Changed the name to NeosPrefix
- You can make free-prefix-ticket with command
- the chatting color is set to the color in your config
- I changed the config type (excluding config.yml)


# 체인지로그 [Master 1.0.1]

- 이름을 MibPrefix에서 NeosPrefix로 변경하였습니다
- 이제 원하는 자유칭호권을 명령어로 만들 수 있습니다
- 채팅 색이 콘피그에 있는 옵션으로 적용됩니다
- config.yml을 제외한 모든 콘피그 파일이 JSON 형식으로 
