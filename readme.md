# DoH �ƃv���C�o�V�[

����ɂ��́A�L���G���W�j�A�̒��R�ł��B

DNS over HTTPS�i�ȉ� DoH�j�𗘗p���邱�Ƃ� User-Agent �� DNS �L���b�V���T�[�o�Ԃ̒ʐM���u�����v�u��₁v�u�Ȃ肷�܂��v�����邱�Ƃ��ł��܂��B

���̌��ʁA�v���C�o�V�[�̕ی�ƃZ�L�����e�B�[�̌��オ���҂ł��܂��B

����̓v���C�o�V�[�̊ϓ_���� DoH �ɂ��Č@�艺���Ă݂����Ǝv���܂��B

�i��01.jpg�j

## DoH �ւ̎^���ƌ��O

��q�̗��R����v���b�g�t�H�[�}�[�� DoH �Ɏ^���������Ă��܂��B

- [Microsoft](https://techcommunity.microsoft.com/t5/Networking-Blog/Windows-will-improve-user-privacy-with-DNS-over-HTTPS/ba-p/1014229)
- [Mozilla](https://blog.mozilla.org/futurereleases/2019/09/06/whats-next-in-making-dns-over-https-the-default/)
- [Google](https://blog.chromium.org/2019/09/experimenting-with-same-provider-dns.html)

���� ISP �� Comcast ��
[���̗���Ɍx������\��](https://www.vice.com/en_us/article/9kembz/comcast-lobbying-against-doh-dns-over-https-encryption-browsing-data)
���܂����B

�v���b�g�t�H�[�}�[�ɂ�� DNS �̏W�������l�X�ȃ��X�N�������N�����A�Ƃ����咣�ł��B

> The unilateral centralization of DNS raises serious policy issues relating to cybersecurity, privacy, antitrust, national security and law enforcement, network performance and service quality (including 5G), and other areas.

�������A���̎咣��
[�v���b�g�t�H�[�}�[����̔���](https://blog.mozilla.org/blog/2019/11/01/asking-congress-to-examine-isp-data-practices/)
���󂯂܂��B

Mozilla �H���A�ނ��� ISP ���f�[�^��Ɛ肵�A���₩�Ȃ�ʗp�r�Ɋ��p���Ă���̂ł͂Ȃ����A�Ƃ����킯�ł��B

> These developments have raised serious questions. How is your browsing data being used by those who provide your internet service? Is it being shared with others? And do consumers understand and agree to these practices? We think it�fs time Congress took a deeper look at ISP practices to figure out what exactly is happening with our data.

�m���� DNS �͋����֐S���̃n�j�[�|�b�g��������܂���B

�׈��� ISP �Ȃ�΃��[�U�[�A�J�E���g�Ɩ��O�����v����R�Â��A�����֐S���Ƃ��Ē~�ρ`���p���邱�Ƃ��\�ł��B���낵��I

�ł� ISP �� DNS ������� DoH �𗘗p����΁A���̃��X�N���瓦��邱�Ƃ��ł���̂ł��傤���H

�܂���
[RFC 8484](https://tools.ietf.org/html/rfc8484)
���m�F���Ă݂܂��B

> HTTP cookies SHOULD NOT be accepted by DOH clients unless they are explicitly required by a use case.

�ǂ���� DoH �ł� Cookie �̗��p�͋֎~����Ă��Ȃ��悤�ł��B

���ۂ�
[cloudflare �� wireformat](https://developers.cloudflare.com/1.1.1.1/dns-over-https/wireformat/)
�ɂ͈ȉ��� Example ��������܂��B

```
HTTP/2 200
date: Fri, 23 Mar 2018 05:14:02 GMT
content-type: application/dns-message
content-length: 49
cache-control: max-age=0
set-cookie: \__cfduid=dd1fb65f0185fadf50bbb6cd14ecbc5b01521782042; expires=Sat, 23-Mar-19 05:14:02 GMT; path=/; domain=.cloudflare.com; HttpOnly
server: cloudflare-nginx
cf-ray: 3ffe69838a418c4c-SFO-DOG
```

�Ƃ������Ƃ� DoH �𗘗p���� User-Agent ���u���E�U�� HTTP Set-Cookie / HTTP Cookie ���J�j�Y���𓥏P����ꍇ�A�׈��ȃT�[�r�X�񋟎҂Ȃ�� Set-Cookie �ŕt�^�������ʏ��Ɩ��O�����v����R�Â��A�����֐S���Ƃ��Ē~�ρ`���p�ł������ł��B�Ȃ�Ƃ������Ƃł��傤�I

## Cookie �Ɋւ������

�ł� Firefox 71.0 ���g���ȉ��̎菇�Ŏ������Ă݂܂��傤�B

### 1. ���O DoH �T�[�r�X��p�ӂ���

����� 127.0.0.1 ��� DoH �����𐶐����鎩�O DoH �T�[�r�X��p�ӂ��܂���
https://some-doh-provider.com/2019/201912/dns/entry/doh.php
�i���͂��̎����ň�Ԏ�Ԃ������̂� DNS ���b�Z�[�W�̉�͂ƍ\�z�ł��� �c�j

### 2. ���O DoH �T�[�r�X�𗘗p���邽�߂� Firefox �̐ݒ��ύX����

�C���^�[�l�b�g�ݒ�ɂ�
- DNS over HTTPS ��L���ɂ���
- �v���o�C�_�[���g�p������ URL�i���O DoH �T�[�r�X�j���w��

�i��02.png�j

about:config �ɂ�
- network.trr.bootstrapAddress �� 127.0.0.1 ���w��B����ɂ�� OS �̃��]���o���X�L�b�v���� DoH �̃h���C���isome-doh-provider.com�j�� 127.0.0.1 �ɉ�������܂�
- network.trr.confirmationNS �� skip ���w��B����ɂ��N�����̓���`�F�b�N���������܂�

�i��03.png�j

network.trr �Ɋւ���ڍׂ� mozilla wiki �����m�F���������B
https://wiki.mozilla.org/Trusted_Recursive_Resolver

### 3. Firefox �œK���ȃy�[�W���{������

ex. https://some-web-service.com/

some-web-service.com �̖��O�����̂���
DoH �v�������O DoH �T�[�r�X�ɑ��M����܂�

�i���ēx�j

```
Host: i27.o.o.i
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:71.0) Gecko/20100101 Firefox/71.0
Accept: application/dns-message
Accept-Language: ja,en-US;q=0.7,en;q=0.3
Accept-Encoding: gzip, deflate, br
Cache-Control: no-store
Content-Type: application/dns-message
Content-Length: 65
Connection: keep-alive

00,00,01,00,00,01,00,00,00,00,00,01,18,73,6f,6d,65,2d,6e,6f,6e,2d,65,78,69,73,74,65,6e,74,2d,64,6f,6d,61,69,6e,03,63,6f,6d,00,00,01,00,01,00,00,29,10,00,00,00,00,00,00,08,00,08,00,04,00,01,00,00
```

���O DoH �T�[�r�X���� Set-Cookie + DoH ����
���ꂪ some-doh-provider.com �h���C���� Cookie �Ƃ��Ĉ�����̂��ǂ���������m�F���܂��B
���Ȃ݂ɍ���� some-web-service.com �� 127.0.0.1 �ɉ������Ă��܂��B

```
Content-Type: application/dns-message
Content-Length: 90
Cache-Control: max-age=0
X-Resolve: some-non-existent-domain.com --> 127.0.0.1
X-Sent-Cookie: (none)
Connection: Close
Set-Cookie: doh=48; Secure; HttpOnly

00,00,81,00,00,01,00,01,00,00,00,00,18,73,6f,6d,65,2d,6e,6f,6e,2d,65,78,69,73,74,65,6e,74,2d,64,6f,6d,61,69,6e,03,63,6f,6d,00,00,01,00,01,18,73,6f,6d,65,2d,6e,6f,6e,2d,65,78,69,73,74,65,6e,74,2d,64,6f,6d,61,69,6e,03,63,6f,6d,00,00,01,00,01,00,00,00,80,00,04,7f,00,00,01
```

https://127.0.0.1/
�� phpinfo ��\������ݒ�ɂ��Ă����̂�
https://some-web-service.com/
�ł��ȉ��̒ʂ�ł��B

### 4. Firefox �Ŏ��O DoH �T�[�r�X�Ǔ����h���C���̃y�[�W���{������

�Ⴆ��

https://some-doh-provider.com/check-cookie.php

���ȉ��̂悤�ȃR���e���c�������ꍇ�A���M���ꂽ Cookie �w�b�_���m�F���܂��B

```
<?php

header('Content-Type: text/plain');
header("Set-Cookie: www=true; Secure; HttpOnly");
$arr = apache_request_headers();
foreach ($arr as $fname => $value) {
	print "{$fname}: {$value}\n";
}

?>
```

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
