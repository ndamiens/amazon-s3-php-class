<?php

use Tpyo\AmazonS3\S3;
use PHPUnit\Framework\TestCase;

class S3Test extends TestCase {
	protected static $s3;
	protected static $buckets = ['test-bucket-s3php', 'test_bucket_s3php'];

	public function testConnect() {
		self::$s3 = new S3($_ENV['TEST_ACCESSKEY'], $_ENV['TEST_SECRETKEY'], true, $_ENV['TEST_ENDPOINT']);

		$this->assertInstanceOf(S3::class, self::$s3);
		self::$s3->setS3RequestOption("useBucketDNS", false);
	}

	/**
	 * @depends testConnect
	 *
	 * test with ceph
	 */
	public function testListBucketEmpty() {
		$t = self::$s3->listBuckets();
		$this->assertCount(0, $t);
	}

	/**
	 * @depends testListBucketEmpty
	 */
	public function testCreateBuckets() {
		foreach (self::$buckets as $bname) {
			$t = self::$s3->putBucket($bname);
			$this->assertTrue($t);
		}
		$t = self::$s3->listBuckets();
		$this->assertCount(2, $t);
	}

	/**
	 * @depends testCreateBuckets
	 */
	public function testPutObject() {
		$bucket = self::$buckets[0];
		$t = self::$s3->putObjectFile(__FILE__, $bucket, "objet/test");
		$this->assertTrue($t);
	}

	/**
	 * @depends testPutObject
	 */
	public function testFetchObject() {
		$bucket = self::$buckets[0];
		$md5_src = md5(file_get_contents(__FILE__));
		$t = self::$s3->getObject($bucket, "objet/test");
		$this->assertObjectHasAttribute('body', $t);

		$md5_s3 = md5($t->body);
		$this->assertEquals($md5_s3, $md5_src);
	}

	/**
	 * @depends testPutObject
	 */
	public function testDeleteObject() {
		$bucket = self::$buckets[0];
		$t = self::$s3->deleteObject($bucket, "objet/test");
		$this->assertTrue($t);
	}

	/**
	 * @depends testDeleteObject
	 */
	public function testDeleteBuckets() {
		foreach (self::$buckets as $bname) {
			$t = self::$s3->deleteBucket($bname);
			$this->assertTrue($t);
		}
		$t = self::$s3->listBuckets();
		$this->assertCount(0, $t);
	}
}