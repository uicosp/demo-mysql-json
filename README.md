# demo-mysql-json

## 点赞优化思路
要解决的问题

- 快速获取用户点赞列表
- 快速获取文章的点赞用户列表

这里主要影响性能的是第二个问题，当用户加载帖子列表页时，需要查询每篇帖子有哪些用户点赞。获取到用户ID后还需要获取每个用户的昵称、头像等信息。

如何解决？

论坛类应用读的人比写的人多，所以我们解决问题的思路是：尽量在读取数据时做最少的联表查询，把读取时需要的数据在写入阶段就准备好。

假设有以下两张表 `user_likes` 用户点赞表和 `forum_thread` 帖子表

	表 user_likes
	----------------------------------
	user_id like_thread_list
	------------------------------------

	表 forum_thread
	----------------------------------
	thread_id forum_id ... like_user_list
	------------------------------------

## 点赞时的写入操作分两步走

1. 将点赞记录插入 `user_likes` 表
2. 将点赞记录插入 `forum_thread` 表

关键是怎么插？

这里的重点是 `like_thread_list` 和 `like_user_list` 字段使用 json 格式（MySQL5.7）

帖子点赞列表 like_user_list 存储的数据看起来像这样

	[
	 {"user_id":1,"nickname":"鱼肚","base64_avatar":"base64encodedimg","time":"2017-07-30 16:45:01"},
	 {"user_id":1,"nickname":"蜂鸟","base64_avatar":"base64encodedimg","time":"2017-07-30 16:45:01"},
	 {"user_id":1,"nickname":"小明","base64_avatar":"base64encodedimg","time":"2017-07-30 16:45:01"},
	]

这里用户头像在插入时以base64编码永久保存，这样在拉取帖子点赞列表时就不需要再根据 user_id 去查询头像信息。

而用户点赞列表 like_thread_list 也同理

	[
	 {"thread_id":1,"subject":"大家来点赞","message":"帖子内容的前60个字符","time":"2017-07-30 16:45:01"},
	 {"thread_id":2,"subject":"大家来搞基","message":"帖子内容的前60个字符","time":"2017-07-30 16:45:01"},
	 {"thread_id":3,"subject":"大家来爬梯","message":"帖子内容的前60个字符","time":"2017-07-30 16:45:01"},
	]

也就是说有N个用户，user_likes表里的记录数 <= N

该方案的瓶颈在于，当 json 字段存储的内容变大后，操作复杂度为 O(n)，测试时当总共1W条记录插入和删除一条记录需要 30ms～40ms（根据每条记录大小有所浮动）

JSON_ARRAY_APPEND：全量替换性能低下 *MySQL暂无优化计划*

JSON_REMOVE：全量替换性能低下 此方法将在 MySQL 8 中得到优化

> Added support in MySQL 8.0.2 for partial, in-place updates of JSON column values, which is more efficient than completely removing an existing JSON value and writing a new one in its place, as was done previously when updating any JSON column. For this optimization to be applied, the update must be applied using JSON_SET(), JSON_REPLACE(), or JSON_REMOVE(). New elements cannot be added to the JSON document being updated; values within the document cannot take more space than previous to the update. See Section 11.6, “The JSON Data Type”, for a detailed discussion of the requirements.

 [https://dev.mysql.com/doc/refman/8.0/en/mysql-nutshell.html](https://dev.mysql.com/doc/refman/8.0/en/mysql-nutshell.html) 

暂时无法高效实现的功能：批量倒序获取首页的点赞列表

需要在 MySQL 8 中借助 $[last] 才能实现，当前的XPath尚不支持

> Added support in MySQL 8.0.2 for ranges such as $[1 to 5] in XPath expressions. Also added support in this version for the last keyword and relative addressing, such that $[last] always selects the last (highest-numbered) element in the array and $[last-1] the next to last element. last and expressions using it can also be included in range definitions; for example, $[last-2 to last-1] returns the last two elements but one from an array. See Searching and Modifying JSON Values, for additional information and examples.