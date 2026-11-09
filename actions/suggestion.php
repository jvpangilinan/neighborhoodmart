<?php
ex("UPDATE item_suggestions SET status='dismissed' WHERE id=?",'i',$id); go('Suggestion dismissed.','?p=admin_catalog');
