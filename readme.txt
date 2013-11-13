=== WP2PCS （WordPress连接到百度网盘） ===
Contributors: frustigor
Donate link: http://wp2pcs.duapp.com
Tags: backup, sync, baidu, personal cloud storage, PCS, 百度网盘
Requires at least: 3.5.1
Tested up to: 3.7.1
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

备份、同步WordPress到百度网盘，把PCS作为网站后备箱。
Backup and sync wordpress to baidu PCS.

== Description ==

WP2PCS顾名思议就是把WordPress和百度网盘（百度个人云存储，PCS）连接在一起的插件。它的两项基本功能就是：将wordpress的
数据库、文件<strong>备份</strong>到百度网盘，以防止由于过失而丢失了网站数据；把百度网盘作为网站的后备箱，
<strong>存放</strong>图片、附件，解决网站空间不够用的烦恼，这个时候，你可以在网站内<strong>直接引用</strong>网盘上
的文件，并提高你的网站SEO和用户体验。

WP2PCS means connect your wordpress to baidu personal cloud storage. With this plugin, you can backup your database
and website files to PCS avoiding data missing forever. And you can also upload your pictures to PCS, and use these
pictures in wordpress easily.

@@@@ as baidu accounts are always used by Chinese, now I have not support English. If you want to use this plugin,
speculate the Chinese words with google translate service.

<strong>说明</strong>

1、版本：本插件同时发行三个版本，当前这个版本为“个人完美版”，另外还有两个版本为“开发者创意版”和“企业专业版”，它们
之间的区别可以在主页 http://wp2pcs.duapp.com 了解。大体上，开发者版中，开发者可以使用自己的百度应用API KEY，从而
使用自己的PCS API服务，并可以为别人提供这类服务；企业版则没有成形的最终可用产品，而是为企业用户特定项目中的这个服务
提供定制开发。<br />
2、普通用户使用时是否选择托管？一般而言，只要拥有百度账户，并且开通了网盘服务的百度用户都可以使用本插件，托管服务是
专门为那些没有百度网盘的用户提供的，网站数据将被托管到WP2PCS的官方网盘内，具体详情请看插件后台的介绍。<br />
3、路径设置：对于有一定wordpress经验的用户对此比较了解，对于那些完全不理解路径设置的用户或许需要花一点时间来理解这
些概念，如果你不懂路径与URL的区别，请先google学习这些知识。<br />
4、前缀：在使用插件的图片外链和文件下载功能时，插件要求你填写一个访问URL的前缀，例如你填写image，那么当用户访问
yourdomain.com/image/test.jpg时，就能正确显示这张图片了，下载链接的前缀也是同样一个道理。

<strong>不适用范围</strong>

* 超大型网站
* 开启MULTISITE的多站点网站
* 网站空间剩余不足三分之一
* 没有读写权限或读写权限受限制的空间（如BAE、SAE不能备份网站文件）
* 服务器memory limit, time limit比较小，又不能自己修改的
* 服务器PHP不支持ZipArchive类

== Installation ==

1、把wp2pcs文件夹上传到/wp-content/plugins/目录<br />
2、在后台插件列表中激活它<br />
3、在“插件-WP2PCS”菜单中，点击授权按钮，等待授权跳转<br />
在授权过程中，如果你已经登录了百度账号，会直接跳转；如果没有登录百度账号，会要求你登录，登录之后一定要勾选同意授权<br />
网盘（PCS）服务，否则无法使用插件中的服务。<br />
4、如果授权成功，你会进入到插件的使用页面。<br />
5、初始化所有信息。

== Frequently Asked Questions ==

= 托管服务是什么？ =

托管服务是专门为那些没有百度网盘账户的朋友准备的，如果你有百度网盘，强烈建议你不要使用托管服务，因为托管服务会将你的
网站资料上传到WP2PCS的官方网盘，虽然我们会尽全力保护你的资料安全，但是由于WP2PCS官方的PCS API token是可以获取的，所以
也存在有可能被一些黑客发掘并恶意使用。

托管的备份数据可以下载和删除，但所上传的图片、附件不能被删除，想要进行转移的唯一办法，目前只能通过
http://wp2pcs.duapp.com 向我们提出申请，经过身份确认之后邮件发送给你。

= 外链图片显示错误 =

首先，请确认，你的网站能够正常使用重写服务，并且所填写的前缀，如image，没有和其他插件或其他程序代码冲突；
其次，如果你的网站不支持重写，那么可以尝试填写如“?image”这样的形式的前缀，这种情况下，中文名文件夹和文件名将不被支持。
最后，请确认你的图片访问合法，即通过插入到文章的形式使用，插件本身设置了防盗链的功能。

== Screenshots ==

1. 让你选择授权方式。the status before you get baidu oauth.
2. 你可以对插件进行设置，确定备份时间和目录。the settings of the plugin.
3. 你可以在媒体管理面板上传附件到百度网盘。how to upload to pcs when you post.

== Changelog ==

= 1.1 =
* 两项基本功能没有变，增加了更多的自定义备份选项。

= 1.0 =
* 基本功能：1、备份到百度网盘；2、保存文件到百度网盘，并可以插入到文章中。

== Upgrade Notice ==

= 1.1 =
修改了一些BUG，更新了备份选项，添加自定义备份，升级到1.1版本。
备份选项：注意，根据自己的网站情况选择特定的备份周期。
备份特定目录或文件：每行一个，当前年月日分别用{year}{month}{day}代替，不能有空格，末尾带/，必须为网站目录路径（包含路径头/www/users/xn--sxry05m.xn--fiqs8s/）。注意，上级目录将包含下级目录，如/www/users/xn--sxry05m.xn--fiqs8s/wp-content/将包含/www/users/xn--sxry05m.xn--fiqs8s/wp-content/uploads/，因此务必不要重复，两个只能填一个，否则会报错。填写了目录或文件列表之后，备份时整站将不备份，只备份填写的列表中的目录或文件。

= 1.0 =
实现基本功能

