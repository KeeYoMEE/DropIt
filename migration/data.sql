insert into users values (null, 'pepe', '7c9e7c1494b2684ab7c19d6aff737e460fa9e98d5a234da1310c97ddf5691834', 'test', null); --pepe

insert into home_notes values (null, 1, 'test1', now(), 'some content1',1, now(), 0);
insert into home_notes values (null, 1, 'test2', now(), 'some content2', 1, now(), 0);
insert into home_notes values (null, 1, 'test3', now(), 'some content3', 0, now(), 0);
insert into home_notes values (null, 1, 'test4', now(), 'some content4', 0, now(), 0);
insert into home_notes values (null, 1, 'test5', now(), 'some content5', 0, now(), 0);

insert into home_hashtags values (null, 'cool');
insert into home_hashtags values (null, 'basic');
insert into home_hashtags values (null, 'random');

insert into home_notes_to_hashtags values ('1', 1);
insert into home_notes_to_hashtags values ('1', 2);
insert into home_notes_to_hashtags values ('1', 3);
insert into home_notes_to_hashtags values ('1', 4);
insert into home_notes_to_hashtags values ('1', 5);
insert into home_notes_to_hashtags values ('2', 1);
insert into home_notes_to_hashtags values ('2', 2);
insert into home_notes_to_hashtags values ('2', 5);
insert into home_notes_to_hashtags values ('3', 1);
insert into home_notes_to_hashtags values ('3', 4);
insert into home_notes_to_hashtags values ('3', 5);