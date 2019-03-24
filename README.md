# generaladmin
这是一款基于ThinkPHP和H-ui.admin开发的通用后台管理程序

部署方法：
1. 将文件解压到服务器目录中，并将站点根目录指向至public_html目录
2. 创建mysql数据库并执行db.sql
3. 修改.env文件中的配置以对应服务器环境
4. 数据表前缀可自行更改，只要保证与.env文件中配置保持一致即可
5. 站点后台访问路径为(yoururl)/admin，前台路径为(yoururl)
6. 数据库中现有后台账号为admin（123456）
7. 本程序对密码加密进行了人工处理，详见application/common.php，可自行更改
8. 本地开发环境为wampserver3.1，其他环境并未做测试

有任何问题请issue
