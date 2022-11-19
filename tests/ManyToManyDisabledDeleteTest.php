<?php

namespace Tests;

use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Stillat\Relationships\Support\Facades\Relate;

class ManyToManyDisabledDeleteTest extends RelationshipTestCase
{
    public function test_many_to_many_deletes_can_be_disabled()
    {
        Relate::clear()
            ->manyToMany('conferences.sponsors', 'sponsors.sponsoring')
            ->allowDelete(false);

        Entry::find('sponsors-1')->set('sponsoring', [
            'conferences-1',
            'conferences-2',
        ])->save();

        Entry::find('sponsors-2')->set('sponsoring', [
            'conferences-2',
        ])->save();

        Entry::find('conferences-1')->delete();

        $this->assertSame(['conferences-1', 'conferences-2'], Entry::find('sponsors-1')->get('sponsoring'));
        $this->assertSame(['conferences-2'], Entry::find('sponsors-2')->get('sponsoring'));

        Entry::find('conferences-2')->delete();

        $this->assertSame(['conferences-1', 'conferences-2'], Entry::find('sponsors-1')->get('sponsoring', []));
        $this->assertSame(['conferences-2'], Entry::find('sponsors-2')->get('sponsoring', []));
    }

    public function test_many_to_many_user_deletes_can_be_disabled()
    {
        Relate::clear()
            ->manyToMany('conferences.conference_users', 'user:user_conferences')
            ->allowDelete(false);

        User::find('user-1')->set('user_conferences', [
            'conferences-1',
            'conferences-2',
        ])->save();

        User::find('user-2')->set('user_conferences', [
            'conferences-1',
        ])->save();

        Entry::find('conferences-1')->delete();

        $this->assertSame(['user-1'], Entry::find('conferences-2')->get('conference_users', []));
        $this->assertSame(['conferences-1', 'conferences-2'], User::find('user-1')->get('user_conferences', []));
        $this->assertSame(['conferences-1'], User::find('user-2')->get('user_conferences', []));
    }

    public function test_many_to_many_term_deletes_can_be_disabled()
    {
        Relate::clear()
            ->manyToMany('term:topics.posts', 'entry:articles.topics')
            ->allowDelete(false);

        Entry::find('articles-1')->set('topics', [
            'topics-one',
            'topics-two',
        ])->save();

        Entry::find('articles-2')->set('topics', [
            'topics-one',
        ])->save();

        Entry::find('articles-3')->set('topics', [
            'topics-two',
        ])->save();

        $this->getTerm('topics-one')->delete();
        $this->getTerm('topics-two')->delete();

        $this->assertSame(['topics-one', 'topics-two'], Entry::find('articles-1')->get('topics', []));
        $this->assertSame(['topics-one'], Entry::find('articles-2')->get('topics', []));
        $this->assertSame(['topics-two'], Entry::find('articles-3')->get('topics', []));
    }
}
