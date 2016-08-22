##
# 将以下内容拷贝到相应的配置下面，只在根目录下安装OpenSNS有效，具体用法和服务器配置有关，请百度
##

##
#在这里要感谢opensns演示站www.opensns.com/tox用户“荒牧師”先生提供的帮助
##


#
# Nginx.txt
# www.opensns.com
# 

#禁止访问 View
if ($request_uri ~* (.*)/?View/(.*)?.html$) {
    return 403;
}

#禁止访问 Uploads中的.php文件
if ($request_uri ~* (.*)/?Uploads/(.*)?.php(.*)?$) {
    return 403;
}



 
#默认情况
# Warning: unsupported_condition at line 134:
#     RewriteCond %{REQUEST_FiLENAME} !-d

# Warning: unsupported_flag at line 136:
#     RewriteRule ^/?(.*)$ /index.php/$1 [QSA,PT,L]
if (!-f $request_filename){
rewrite ^/(.*)$ /index.php?s=$1 last;
break;
}