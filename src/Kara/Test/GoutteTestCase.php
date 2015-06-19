<?php

namespace Kara\Test;


use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Yaml\Parser;

class GoutteTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function staticHtmlGoodCase()
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html>
    <head>
        <title>Hello World Page</title>
    </head>
    <body>
        <p class="message">Hello World!</p>
        <p>Hello Crawler!</p>
    </body>
</html>
HTML;

        $crawler = new Crawler($html);

        $crawler->filter('title')->each(
            function (Crawler $node) {
                $this->assertSame('Hello World Page', $node->text());
            }
        );

        $crawler->filter('body p.message')->each(
            function (Crawler $node) {
                $this->assertSame('Hello World!', $node->text());
            }
        );

        $crawler->filter('body p.message ~ p')->each(
            function (Crawler $node) {
                $this->assertSame('Hello Crawler!', $node->text());
            }
        );
    }

    /**
     * @test
     */
    public function symfonyBlogGoodCase()
    {
        $client = new Client();
        $crawler = $client->request('GET', 'http://www.symfony.com/blog/');
        $crawler->filter('title')->each(
            function (Crawler $node) {
                $this->assertSame('The Symfony Blog', $node->text());
            }
        );
    }

    /**
     * @test
     */
    public function faceBookGoodCase()
    {
        $yaml = new Parser();
        $parameters = $yaml->parse(file_get_contents(__DIR__ . '/../../../app/config/parameters.yml'));

        $client = new Client();

        //Facebook トップページ表示
        $crawler = $client->request('GET', 'https://ja-jp.facebook.com');
        $this->assertSame('Facebook - フェイスブック - ログイン (日本語)', $crawler->filter('title')->first()->text());

        //ログインボタンクリック
        $form = $crawler->selectButton('ログイン')->form();
        $crawler = $client->submit(
            $form,
            [
                'email' => $parameters['facebook']['email'],
                'pass' => $parameters['facebook']['password']
            ]
        );

        //友達を検索リンクをクリック
        $crawler->filter('#findFriendsNav')->link();

        //友達を検索ページ表示
        $crawler->filter('h2')->each(
            function (Crawler $node) {
                $this->assertSame('知り合いかも', $node->text());
            }
        );
    }

    public function loadUserFixture()
    {
        $yaml = new Parser();
        $parameters = $yaml->parse(file_get_contents(__DIR__ . '/../../../app/config/parameters.yml'));

        $config = new Configuration([\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC]);
        $conn = DriverManager::getConnection($parameters['database'], $config);

        $conn->exec('SET foreign_key_checks = 0');

        $conn->exec('truncate table user');

        $sql = <<<TXT
INSERT INTO user (email, name)
VALUES (:email, :name)
TXT;
        $stmt = $conn->prepare($sql);

        $stmt->bindValue(':email', 'hoge@example.com');
        $stmt->bindValue(':name', 'fuga');
        $stmt->execute();

        $conn->exec('SET foreign_key_checks = 1');
    }
}