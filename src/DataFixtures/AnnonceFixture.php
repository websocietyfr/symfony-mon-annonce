<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Annonce;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AnnonceFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $user = $manager->getRepository(User::class)->find(13);

        for($i = 0; $i < 10; $i++) {
            $annonce = new Annonce();
            $annonce->setTitle($faker->title);
            $annonce->setDescription($faker->realText);
            $annonce->setPrice($faker->randomNumber(2));
            $annonce->setPriceType('ttc');
            $annonce->setPicture('/upload/img/product.png');
            $annonce->setPegi($faker->randomNumber(2));
            $annonce->setUser($user);

            $manager->persist($annonce);
        }

        // $annonce = new Annonce();
        // $annonce->title = $faker->title;
        // $annonce->description = $faker->text;
        // $annonce->price = $faker->randomNumber(2);
        // $annonce->price_type = 'ttc';
        // $annonce->picture = '/upload/img/product.png';
        // $annonce->pegi = 16;
        // $annonce->user_id = 13;
        // $manager->persist($annonce);

        $manager->flush();
    }
}
