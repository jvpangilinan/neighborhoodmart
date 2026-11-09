<?php
ex("INSERT INTO announcements(title,body) VALUES(?,?)",'ss',post('title'),post('body')); audit('posted announcement'); go('Posted.','?p=admin_announce');
