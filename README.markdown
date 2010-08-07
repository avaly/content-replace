# content-replace

content-replace is a WordPress plugin that finds and replaces text through the database in multiple places. It's useful when moving a WordPress installation to a new domain/URL and there are hardcoded URLs in the database.

It will perform the find and replace operation on the following:

+ table: *posts* - field: *post_content*
+ table: *posts* - field: *guid*
+ table: *postmeta* - field: *meta_value*
+ content of *text widgets*
