# DoH + Set-Cookie �ŋ����֐S���ۗ��I�H

����ɂ��� ~~Cookie �G���W�j�A~~ �L���G���W�j�A�̒��R�ł��B<br />
�v���b�g�t�H�[�}�[�̃|���V�[��@�K���̋����ɔ����A�܂��܂������̌������Ȃ� 3rd-party Cookie �ł����A����� DNS over HTTPS�i�ȉ� DoH�j�� Set-Cookie �̑g�ݍ��킹�ɂ��Č@�艺���Ă݂����Ǝv���܂��B<br />

![](01.jpg)

## DoH �ւ̎^���ƌ��O

DoH �𗘗p���邱�Ƃ� User-Agent �� DNS �L���b�V���T�[�o�Ԃ̒ʐM���u�����v�u��₁v�u�Ȃ肷�܂��v�����邱�Ƃ��ł��܂��B<br />
�v���C�o�V�[�ی�ƃZ�L�����e�B�[����ւ̊��҂���A�v���b�g�t�H�[�}�[�� DoH �Ɏ^���������Ă��܂��B<br />

- [Microsoft](https://techcommunity.microsoft.com/t5/Networking-Blog/Windows-will-improve-user-privacy-with-DNS-over-HTTPS/ba-p/1014229)
- [Mozilla](https://blog.mozilla.org/futurereleases/2019/09/06/whats-next-in-making-dns-over-https-the-default/)
- [Google](https://blog.chromium.org/2019/09/experimenting-with-same-provider-dns.html)

�Ƃ��낪 ISP �� Comcast �͂��̗���� [�x������\��](https://www.vice.com/en_us/article/9kembz/comcast-lobbying-against-doh-dns-over-https-encryption-browsing-data) ���܂����B<br />
�v���b�g�t�H�[�}�[�ɂ�� DNS �̏W�������l�X�ȃ��X�N�������N�����A�Ƃ����咣�ł��B<br />

> The unilateral centralization of DNS raises serious policy issues relating to cybersecurity, privacy, antitrust, national security and law enforcement, network performance and service quality (including 5G), and other areas.

�������A���̎咣�� [�v���b�g�t�H�[�}�[�̔���](https://blog.mozilla.org/blog/2019/11/01/asking-congress-to-examine-isp-data-practices/) ���󂯂܂��B<br />
Mozilla �H���A�ނ��� ISP ���f�[�^��Ɛ肵�A���₩�Ȃ�ʗp�r�Ɋ��p���Ă���̂ł͂Ȃ����A�Ƃ����킯�ł��B<br />

> These developments have raised serious questions. How is your browsing data being used by those who provide your internet service? Is it being shared with others? And do consumers understand and agree to these practices? We think it's time Congress took a deeper look at ISP practices to figure out what exactly is happening with our data.

�m���� DNS �͋����֐S���̃n�j�[�|�b�g�ł��B<br />
�׈��� ISP �Ȃ�΃��[�U�[�A�J�E���g�Ɩ��O�����v����R�Â��A�����֐S���Ƃ��Ē~�ρ`���p���邱�Ƃ��\�ł��B���낵��I<br />

�ł� ISP �� DNS ������� DoH �𗘗p����΁A���̃��X�N���瓦��邱�Ƃ��ł���̂ł��傤���H<br />
[RFC 8484](https://tools.ietf.org/html/rfc8484)���m�F���Ă݂܂��傤�B<br />

> HTTP cookies SHOULD NOT be accepted by DOH clients unless they are explicitly required by a use case.

�ǂ���� DoH �ł� Cookie �̗��p�͋֎~����Ă��Ȃ��悤�ł��B<br />
���ۂ� [cloudflare �� Example](https://developers.cloudflare.com/1.1.1.1/dns-over-https/wireformat/) ����� set-cookie ��������܂��B<br />

'''http
HTTP/2 200
date: Fri, 23 Mar 2018 05:14:02 GMT
content-type: application/dns-message
content-length: 49
cache-control: max-age=0
set-cookie: \__cfduid=dd1fb65f0185fadf50bbb6cd14ecbc5b01521782042;
    expires=Sat, 23-Mar-19 05:14:02 GMT; path=/; domain=.cloudflare.com; HttpOnly
server: cloudflare-nginx
cf-ray: 3ffe69838a418c4c-SFO-DOG
'''

�Ƃ������Ƃ� DoH �𗘗p���� User-Agent ���u���E�U�� HTTP Set-Cookie / HTTP Cookie ���J�j�Y���𓥏P����ꍇ�A�׈��ȃT�[�r�X�񋟎҂Ȃ�� Set-Cookie �ŕt�^�������ʏ��Ɩ��O�����v����R�Â��A�����֐S���Ƃ��Ē~�ρ`���p�ł������ł��B�Ȃ�Ƃ������Ƃł��傤�I<br />

## Set-Cookie �͗L�����H

�ł� Firefox 71.0 ���g���A�ȉ��̎菇�Ŏ������Ă݂܂��傤�B<br />

### 1. ���O DoH �T�[�r�X��p��

����� 127.0.0.1 ��� DoH �����𐶐����鎩�O DoH �T�[�r�X�idoh.php�j��p�ӂ��܂����B<br />
���킹�ĐÓI�� HTML �t�@�C���ihello.html�j�� Cookie �̑���M���m�F����R���e���c�icheck-cookie.php�j���p�ӂ��܂��B<br />

``` {.dark}
https://127.0.0.1/[doh.php](��)
https://127.0.0.1/[hello.html](��)
https://127.0.0.1/[check-cookie.php](��)
```

### 2. Firefox �̐ݒ��ύX

�C���^�[�l�b�g�ݒ�ɂ� ...<br />

- DNS over HTTPS ��L���ɂ���B
- �v���o�C�_�[���g�p������ URL�i���O DoH �T�[�r�X�j���w�肷��B

![]�i��02.png�j

about:config �ɂ� ...<br />

- network.trr.bootstrapAddress �� 127.0.0.1 ���w�肷��B<br />����ɂ�� OS �̖��O�������X�L�b�v���Ď��O DoH �T�[�r�X�̃h���C���� 127.0.0.1 �ɉ�������܂��B
- network.trr.confirmationNS �� skip ���w�肷��B<br />����ɂ��N�����̓���`�F�b�N���������܂��B

![]�i��02.png�j

�Ȃ� network.trr �Ɋւ���ڍׂ� [mozilla wiki](https://wiki.mozilla.org/Trusted_Recursive_Resolver) �����m�F���������B

### 3. Firefox �� hello.html ���{������

``` {.dark}
https://my-test-web-service.com/hello.html
```
my-test-web-service.com �̖��O�����̂��߂� DoH �v�������O DoH �T�[�r�X�ɑ��M����܂��B<br />

```http {.dark}
Host: i27.o.o.i
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:71.0) Gecko/20100101 Firefox/71.0
Accept: application/dns-message
Accept-Language: ja,en-US;q=0.7,en;q=0.3
Accept-Encoding: gzip, deflate, br
Cache-Control: no-store
Content-Type: application/dns-message
Content-Length: 65
Connection: keep-alive

00,00,01,00,00,01,00,00,00,
00,00,01,18,73,6f,6d,65,2d,
6e,6f,6e,2d,65,78,69,73,74,
65,6e,74,2d,64,6f,6d,61,69,
6e,03,63,6f,6d,00,00,01,00,
01,00,00,29,10,00,00,00,00,
00,00,08,00,08,00,04,00,01,
00,00
```

���O DoH �T�[�r�X���� Set-Cookie + DoH ����
���ꂪ some-doh-provider.com �h���C���� Cookie �Ƃ��Ĉ�����̂��ǂ���������m�F���܂��B
���Ȃ݂ɍ���� some-web-service.com �� 127.0.0.1 �ɉ������Ă��܂��B

```http {.dark}
Content-Type: application/dns-message
Content-Length: 90
Cache-Control: max-age=0
X-Resolve: some-non-existent-domain.com --> 127.0.0.1
X-Sent-Cookie: (none)
Connection: Close
Set-Cookie: doh=48; Secure; HttpOnly

00,00,81,00,00,01,00,01,00,
00,00,00,18,73,6f,6d,65,2d,
6e,6f,6e,2d,65,78,69,73,74,
65,6e,74,2d,64,6f,6d,61,69,
6e,03,63,6f,6d,00,00,01,00,
01,18,73,6f,6d,65,2d,6e,6f,
6e,2d,65,78,69,73,74,65,6e,
74,2d,64,6f,6d,61,69,6e,03,
63,6f,6d,00,00,01,00,01,00,
00,00,80,00,04,7f,00,00,01
```

https://127.0.0.1/
�� phpinfo ��\������ݒ�ɂ��Ă����̂�
https://some-web-service.com/
�ł��ȉ��̒ʂ�ł��B

3. Firefox �Ŏ��O DoH �T�[�r�X�Ǔ����h���C���̃y�[�W���{������

�Ⴆ��

https://some-doh-provider.com/check-cookie.php

���ȉ��̂悤�ȃR���e���c�������ꍇ�A���M���ꂽ Cookie �w�b�_���m�F���܂��B

<?php

header('Content-Type: text/plain');
header("Set-Cookie: www=true; Secure; HttpOnly");
$arr = apache_request_headers();
foreach ($arr as $fname => $value) {
	print "{$fname}: {$value}\n";
}

?>

�i�����ʁj

Set-Cookie: doh=true; Secure; HttpOnly
�͑��M����܂���ł����B
����� check-cookie.php �� Set-Cookie �����l�͗L���ł��B

�i��DB �̒l�j

Firefox 71.0 �̎����ł� DoH ������ Set-Cookie �͖�������邽�߁A
���� DoH �T�[�r�X�񋟎҂��׈��ł����Ă��A�����֐S���̒~�ρ`���p�͓���Ƃ������Ƃ��m�F�ł��܂����B
���̃P�[�X�ɂ����Ă� DoH �̗��p�͏]�������v���C�o�V�[�Z�[�t�ł���A�ƌ��������ł��B
�F��������̃u���E�U��p���Ď������Ă݂Ă��������B

* DoH �̉\��

�Ƃ��� Web �A�v���P�[�V�����̊J���`�e�X�g�̍ۂɂ͂��΂��� hosts ��ύX���܂��B
�����āA���܂ɐݒ�~�X�⌳�ɖ߂��̂�Y��ăn�}��l�����X�������܂��B
DoH ���p�̉\���Ƃ��āA
�������Ńe�X�g�����Ă���O���[�v�����̐ݒ�����O DoH �ō���āA
�e�X�g���{�҂̓u���E�U�� DoH �� on/off �ŗ��p�������؂�ւ���A
�Ƃ����^�p������΁i��q�̃~�X�������āj���Y�������߂���悤�Ȃȁ`�Ǝv��������ł��B

�i��낵����� techscore �̃v���C�o�V�[�֘A�L�������ǂ݂��������j
https://www.techscore.com/blog/tag/privacy/
