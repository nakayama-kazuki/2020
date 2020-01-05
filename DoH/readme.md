# DoH + Set-Cookie �ŋ����֐S���ۗ��I�H

����ɂ��́A�L���G���W�j�A�̒��R�ł��B<br />
�F����͊��� DNS over HTTPS�i�ȉ� DoH�j��������܂������H<br />
DoH �𗘗p���邱�Ƃ� User-Agent �� DNS �L���b�V���T�[�o�Ԃ̒ʐM���u�����v�u��₁v�u�Ȃ肷�܂��v�����邱�Ƃ��ł��܂��B<br />

[Mozilla �ɂ���](https://wiki.mozilla.org/Trusted_Recursive_Resolver)<br />

> DNS-over-HTTPS (DoH) allows DNS to be resolved with enhanced privacy, secure transfers and improved performance.

�������ł��B<br />
�܂�
[Microsoft](https://techcommunity.microsoft.com/t5/Networking-Blog/Windows-will-improve-user-privacy-with-DNS-over-HTTPS/ba-p/1014229)
��
[Google](https://blog.chromium.org/2019/09/experimenting-with-same-provider-dns.html)
�� DoH �̗̍p�Ɏ^�����Ă��܂��B<br />

![](01.jpg)

## DoH �ւ̌��O

�Ƃ��낪 ISP �� Comcast �͂��������v���b�g�t�H�[�}�[�̓����� [�x������\��](https://www.vice.com/en_us/article/9kembz/comcast-lobbying-against-doh-dns-over-https-encryption-browsing-data) ���܂����B<br />
�v���b�g�t�H�[�}�[�ɂ�� DNS �̏W�������l�X�ȃ��X�N�������N�����A�Ƃ̎咣�ł��B<br />

> The unilateral centralization of DNS raises serious policy issues relating to cybersecurity, privacy, antitrust, national security and law enforcement, network performance and service quality (including 5G), and other areas.

�������A���̎咣�� [����](https://blog.mozilla.org/blog/2019/11/01/asking-congress-to-examine-isp-data-practices/) ���󂯂܂��B<br />
Mozilla �H���A�ނ��� ISP ���f�[�^��Ɛ肵�A���₩�Ȃ�ʗp�r�Ɋ��p���Ă���̂ł͂Ȃ����A�Ƃ����킯�ł��B<br />

> These developments have raised serious questions. How is your browsing data being used by those who provide your internet service? Is it being shared with others? And do consumers understand and agree to these practices? We think it's time Congress took a deeper look at ISP practices to figure out what exactly is happening with our data.

�m���� DNS �͋����֐S���̕�ɂƌ����܂��B<br />
�׈��� ISP �Ȃ�΃��[�U�[�A�J�E���g�Ɩ��O�����v����R�Â��A�����֐S���Ƃ��Ē~�ρ`���p���邱�Ƃ��\�ł��B�������낵���I<br />

�ł� ISP �� DNS ��������� DoH �𗘗p����΁A���̃��X�N���瓦��邱�Ƃ��ł���̂ł��傤���H<br />
������ [RFC 8484](https://tools.ietf.org/html/rfc8484)���m�F���Ă݂܂��傤�B<br />

> HTTP cookies SHOULD NOT be accepted by DOH clients unless they are explicitly required by a use case.

DoH �ł� Cookie �̗��p�͋֎~����Ă��Ȃ��悤�ł��B<br />
cloudflare �� [Example](https://developers.cloudflare.com/1.1.1.1/dns-over-https/wireformat/) ����� Set-Cookie ��p�����Ⴊ������܂��B<br />

```http
HTTP/2 200
date: Fri, 23 Mar 2018 05:14:02 GMT
content-type: application/dns-message
content-length: 49
cache-control: max-age=0
set-cookie: \__cfduid=dd1fb65f0185fadf50bbb6cd14ecbc5b01521782042;
    expires=Sat, 23-Mar-19 05:14:02 GMT; path=/; domain=.cloudflare.com; HttpOnly
server: cloudflare-nginx
cf-ray: 3ffe69838a418c4c-SFO-DOG
```

�Ƃ������Ƃ� DoH �𗘗p���� User-Agent �� Web �u���E�W���O�� HTTP Set-Cookie / HTTP Cookie ���J�j�Y���𓥏P����ꍇ�ASet-Cookie �ŕt�^�������ʏ��Ɩ��O�����v����R�Â��A�����֐S���Ƃ��Ē~�ρ`���p���邱�Ƃ��ł��Ă��܂������ł��B�Ȃ�Ƃ������Ƃł��傤�I<br />

## DoH + Set-Cookie �͋@�\���邩�H

�ł� Firefox 71.0 ���g���Ď������Ă݂܂��傤�B<br />
�A�E�g���C���͈ȉ��̒ʂ�ł��B<br />

1. ���O DoH �T�[�r�X�̏���<br />�� ttps://test.doh/doh.php
2. Firefox �̐ݒ�ύX
3. �K���ȃh���C���̃R���e���c���{��<br />�� ttps://test.www/hello.html<br />������ 1. �ɂ�� test.www �̖��O���� + Set-Cookie !!
4. ���O DoH �T�[�r�X�Ɠ����h���C���̃R���e���c���{��<br />�� ttps://test.doh/request-headers.php<br />������ 3. �ɂ�� Set-Cookie �̌��ʂ��m�F

### 1. ���O DoH �T�[�r�X�̏���

���O DoH �T�[�r�X�� [�T���v���R�[�h�͂�����](doh.php) �ł��B<br />
����̃e�X�g�ł͂���� 127.0.0.1 �� DocumentRoot ��œ������܂��B<br />

### 2. Firefox �̐ݒ�ύX

1. �\�� ttps://test.doh/ �ɑ΂���Z�L�����e�B�[��O�����F����
2. Cookie �̃u���b�N�@�\�͎g��Ȃ��̂Ń`�F�b�N���O��

![](04.png)

3. about:config �ɂĐݒ��ύX���� DoH ��L���ɂ���<br />

| network.trr �̐ݒ�    | �ύX�O�̒l                                    | �ύX��̒l                | �ύX��̈Ӗ�                  |
| ---                   | ---                                           | ---                       | ---                           |
| mode                  | 0                                             | 3                         | ���O������ DoH �̂ݗ��p       |
| uri                   | ttps://mozilla.cloudflare-dns.com/dns-query   | ttps://test.doh/doh.php   | DoH �G���g���[                |
| bootstrapAddress      | �i�Ȃ��j                                      | 127.0.0.1                 | test.doh �� 127.0.0.1 �ɉ���  |
| confirmationNS        | example.com                                   | skip                      | �N�����̓���`�F�b�N������    |

### 3. �K���ȃh���C���̃R���e���c���{��

���ɓK���ȃh���C���̃R���e���c�ittps://test.www/hello.html�j���{�����܂��B<br />
���̂Ƃ� test.www �̖��O�����̂��߂� DoH �v�������O DoH �T�[�r�X�ɑ��M����܂��B<br />
�� �G���e�B�e�B�[�{�f�B�[�����́A���ۂ� [RFC 1035](https://tools.ietf.org/html/rfc1035) �Œ�`�����p�P�b�g�t�H�[�}�b�g�ł�<br />

```http
Host: test.doh
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:71.0) Gecko/20100101 Firefox/71.0
Accept: application/dns-message
Accept-Language: ja,en-US;q=0.7,en;q=0.3
Accept-Encoding: gzip, deflate, br
Cache-Control: no-store
Content-Type: application/dns-message
Content-Length: 45
Connection: keep-alive

00,00,01,00,00,01,00,00,
00,00,00,01,04,74,65,73,
74,03,77,77,77,00,00,01,
00,01,00,00,29,10,00,00,
00,00,00,00,08,00,08,00,
04,00,01,00,00
```

���O DoH �T�[�r�X�� DoH �����Ƃ��킹�ăe�X�g�p�� Set-Cookie ���������܂��B<br />

```http
Content-Type: application/dns-message
Content-Length: 50
Cache-Control: max-age=0
X-Resolve: test.www --> 127.0.0.1
X-Sent-Cookie: (none)
Connection: Close
Set-Cookie: doh=49; expires=Saturday, 11-Jan-2020 06:43:14 CET; Secure; HttpOnly

00,00,81,00,00,01,00,01,
00,00,00,00,04,74,65,73,
74,03,77,77,77,00,00,01,
00,01,04,74,65,73,74,03,
77,77,77,00,00,01,00,01,
00,00,00,80,00,04,7f,00,
00,01
```

����� test.www �� [127.0.0.1 �ɉ���](https://github.com/nakayama-kazuki/2020/blob/master/DoH/doh.php#L420) ���� 127.0.0.1 �� DocumentRoot ��� hello.html ���\������܂����B<br />

![](05.png)

### 4. ���O DoH �T�[�r�X�Ɠ����h���C���̃R���e���c���{��

�Ō�Ɏ��O DoH �T�[�r�X�Ɠ����h���C���̃R���e���c�ittps://test.doh/request-headers.php�j���{�����܂��B<br />
���Ȃ݂� request-headers.php �͈ȉ��̂悤�ȊȒP�ȃX�N���v�g�ł��B<br />

```php
header('Content-Type: text/plain');
$headers = apache_request_headers();
foreach ($headers as $field => $value) {
	print "{$field}: {$value}\n";
}
```

��������̎��O DoH �T�[�r�X�����

```http
Set-Cookie: doh=49; expires=Saturday, 11-Jan-2020 06:43:14 CET; Secure; HttpOnly
```

���@�\����Ȃ�� Firefox �̓R���e���c�{���̍ۂ�

```http
Cookie: doh=49
```

�𑗐M���A���̓��e���\�������͂��ł����A�ʂ����Č��ʂ� ...<br />

![](06.png)

�����̒ʂ� Firefox �� Cookie �𑗐M���܂���ł����B<br />
�܂� Cookie ���ۑ�����Ă��܂���ł����B<br />

![](07.png)

������ test.doh �� Cookie ��ێ�������Ԃł� DoH �v���̍ۂɂ� Firefox �� Cookie �𑗐M���܂���ł����B<br />

## �܂Ƃ�

Firefox 71.0 �̎����ł� DoH ������ Set-Cookie �͖�������A�܂� DoH �v���ł� Cookie �͑��M����Ȃ����߁A�׈��� DoH �T�[�r�X�񋟎҂ł����Ă��A�����֐S���̒~�ρ`���p�͓���Ƃ������Ƃ��m�F�ł��܂����B<br />
�̂� DoH �𗘗p�������O�����͏]���̕��@�����v���C�o�V�[�Z�[�t�ł���A�ƌ��_�t���邱�Ƃ��ł��܂����B<br />
�F��������̃u���E�U��p���Ď������Ă݂Ă��������B<br />

�]�k�ł��� Web �A�v���P�[�V�����̊J���`�e�X�g�̍ۂɂ͂��΂��� hosts ��ύX���܂����A���܂ɐݒ�~�X�⌳�ɖ߂��̂�Y��ăn�}��l���������܂��B�������ŊJ���`�e�X�g�����Ă���O���[�v�����̐ݒ�� DoH �T�[�r�X�Œ񋟂��A�e�X�g���{�҂� User-Agent �� DoH �� on/off ���邱�Ƃŗ��p�������؂�ւ���A�������̓e�X�g��p�̃u���E�U�ł̂� DoH ���g�� ... �Ȃ�ĉ^�p�ɂ��i�O�q�̃~�X�������āj���Y�������߂�ꂻ���ł��ˁB<br />

����ɗ]�k�ł��� Cookie ��������肵�������� [techscore blog](https://www.techscore.com/blog/author/nakayama-kazuki/) ���ǂ��� :-p
