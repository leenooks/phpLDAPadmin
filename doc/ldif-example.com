# extended LDIF
#
# LDAPv3
# base <dc=example.com> with scope subtree
# filter: (objectclass=*)
# requesting: ALL
#

# example.com
dn: dc=example.com
dc: example.com
objectClass: dNSDomain

# Bad DNs, example.com
dn: ou=Bad DNs,dc=example.com
ou: Bad DNs
objectClass: organizationalUnit

# double plus \2B\2B, Bad DNs, example.com
dn: c=double plus \2B\2B,ou=Bad DNs,dc=example.com
c: double plus ++
objectClass: country

# end dollar$, Bad DNs, example.com
dn: c=end dollar$,ou=Bad DNs,dc=example.com
c: end dollar$
objectClass: country

# multi + value, Bad DNs, example.com
dn: uid=multi+uid=value,ou=Bad DNs,dc=example.com
cn: Test
sn: Test
uid: multi
uid: value
objectClass: inetOrgPerson

# quote\22double, Bad DNs, example.com
dn: uid=quote\22double,ou=Bad DNs,dc=example.com
cn: Test
sn: Test
uid: quote"double
objectClass: inetOrgPerson

# quote'single, Bad DNs, example.com
dn: uid=quote'single,ou=Bad DNs,dc=example.com
cn: Test
sn: Test
uid: quote'single
objectClass: inetOrgPerson

# angle\3Cleft, Bad DNs, example.com
dn: uid=angle\3Cleft,ou=Bad DNs,dc=example.com
cn: Test
sn: Test
uid: angle<left
objectClass: inetOrgPerson

# angle\3Eright, Bad DNs, example.com
dn: uid=angle\3Eright,ou=Bad DNs,dc=example.com
cn: Test
sn: Test
uid: angle>right
objectClass: inetOrgPerson

# sign@at, Bad DNs, example.com
dn: uid=sign@at,ou=Bad DNs,dc=example.com
cn: Test
sn: Test
uid: sign@at
objectClass: inetOrgPerson

# sign\3Bsemicolon@at, Bad DNs, example.com
dn: uid=sign\3Bsemicolon@at,ou=Bad DNs,dc=example.com
cn: Test
sn: Test
uid: sign;semicolon@at
objectClass: inetOrgPerson

# sign?question, Bad DNs, example.com
dn: uid=sign?question,ou=Bad DNs,dc=example.com
cn: Test
sn: Test
uid: sign?question
objectClass: inetOrgPerson

# sign\2Ccomma, Bad DNs, example.com
dn: uid=sign\2Ccomma,ou=Bad DNs,dc=example.com
cn: Test
sn: Test
uid: sign,comma
objectClass: inetOrgPerson

# brace(left, Bad DNs, example.com
dn: uid=brace(left,ou=Bad DNs,dc=example.com
cn: Test
sn: Test
uid: brace(left
objectClass: inetOrgPerson

# brace)right, Bad DNs, example.com
dn: uid=brace)right,ou=Bad DNs,dc=example.com
cn: Test
sn: Test
uid: brace)right
objectClass: inetOrgPerson

# sign%percent, Bad DNs, example.com
dn: uid=sign%percent,ou=Bad DNs,dc=example.com
cn: Test
sn: Test
uid: sign%percent
objectClass: inetOrgPerson

# sign\3Dequal, Bad DNs, example.com
dn: uid=sign\3Dequal,ou=Bad DNs,dc=example.com
uid: sign=equal
cn: Test
sn: Test
objectClass: inetOrgPerson

# sign\2Bplus, Bad DNs, example.com
dn: uid=sign\2Bplus,ou=Bad DNs,dc=example.com
cn: Test
sn: Test
uid: sign+plus
objectClass: inetOrgPerson

# colon\3Bsemi, Bad DNs, example.com
dn: uid=colon\3Bsemi,ou=Bad DNs,dc=example.com
cn: Test
sn: Test
uid: colon;semi
objectClass: inetOrgPerson

# colon:full, Bad DNs, example.com
dn: uid=colon:full,ou=Bad DNs,dc=example.com
cn: Test
sn: Test
uid: colon:full
objectClass: inetOrgPerson

# multi + sign@at, Bad DNs, example.com
dn: uid=multi+uid=sign@at,ou=Bad DNs,dc=example.com
cn: Test
sn: Test
uid: multi
uid: sign@at
objectClass: inetOrgPerson

# sign@at + multi-mixed, Bad DNs, example.com
dn: sn=sign@at+uid=multi-mixed,ou=Bad DNs,dc=example.com
cn: Test
uid: multi-mixed
sn: sign@at
objectClass: inetOrgPerson

# Non English Chars, example.com
dn: ou=Non English Chars,dc=example.com
ou: Non English Chars
objectClass: organizationalUnit
objectClass: top

# \D0\A7\D0\B5\D0\BB\D0\BE\D0\B2\D0\B5\D0\BA\D0\B8, Non English Chars, exampl
 e.com
dn:: Y2490KfQtdC70L7QstC10LrQuCxvdT1Ob24gRW5nbGlzaCBDaGFycyxkYz1leGFtcGxlLmNvb
 Q==
cn:: 0KfQtdC70L7QstC10LrQuA==
objectClass: inetOrgPerson
objectClass: top
sn:: 0KfQtdC70L7QstC10LrQuA==

# \D0\94\D0\B5\D0\B4 \D0\9B\D0\BE\D0\B3\D0\BE\D0\BF\D0\B5\D0\B4, \D0\A7\D0\B5
 \D0\BB\D0\BE\D0\B2\D0\B5\D0\BA\D0\B8, Non English Chars, example.com
dn:: Y2490JTQtdC0INCb0L7Qs9C+0L/QtdC0LGNuPdCn0LXQu9C+0LLQtdC60Lgsb3U9Tm9uIEVuZ
 2xpc2ggQ2hhcnMsZGM9ZXhhbXBsZS5jb20=
cn:: 0JTQtdC0INCb0L7Qs9C+0L/QtdC0
givenName:: 0JTQtdC0
jpegPhoto:: /9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAMCAgMCAgMDAwMEAwMEBQgFBQQEBQoHBw
 YIDAoMDAsKCwsNDhIQDQ4RDgsLEBYQERMUFRUVDA8XGBYUGBIUFRT/2wBDAQMEBAUEBQkFBQkUDQs
 NFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBT/wAARCAAw
 AEADASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEA
 wUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKS
 o0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqK
 jpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QA
 HwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEB
 SExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSE
 lKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba
 3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD9A/gj
 8Nx8IvhN4V8KPKsr6PpkNnJIv3WZV+Y/QnNc/wCINDutR1Q/Mux2yxHTFeTaP+17d+IfDN94w02OD
 W/ANsF+0apZKZHtHOMxyKPmDDK8Ed69G8IfEHR/ij4aTxD4cv4760VMkxEjacdGBAKn2IoA5Txn4K
 uIzO0v+rToyDjmvlL486HBb2jhdzEdNvrzXtvxB+NsNjeXdpFM7JFlQpbv6GvlD4qfE6/1Ca4nktW
 kgI24Tkd+goA+b/ESSRXkoJIwejCuWuZFfOQAfUV1HiC9vNVuGmj064RJOjvC4HHpxXGXwntZT50T
 p6FlIoAy9UQLE5A7VzDJ5h/Guj1aUi1ZvXisTyTGuDwR1oA/cX/gnT8CPFfwl/Z21nR/Gugpo9/rO
 pSXS6df7XdrdoUQCZATtJKt8p5xjPWvefh98HdE8D3d3qcNnb2lzPGYXisVaO2EI6IIySOPWvRulY
 XjjWYvD/hLVdQmfy4oLd3ZvQAUAfjx+0H42kHxR8TLajybdb2VY4lPAAY4FefeGbx9Q1yzlu7f+0r
 bdmSEt8x9QAeK1PiQi614n1LUlbi4neXn3Ymud0m9l0G6jlVC6E5OOoPqKAIPFPhq88O+Mr7V9J1i
 /trC4kMyWaiRHyf4Sn3BjpnJGKpT6yNe0lk1W0jN2DgTBAN49T716NqOrXXiC1807dm3rIcV5j4mb
 7KrZYbsHkUAeW+JIEt7hIYwCqtvwPb/ACKwXBySec1v39nPc3DTshCNwmR1HrVGfT2jIJHFAH9Gkf
 xQspdds7KKOWaGZtr3CITHEMcMzdAM4/OuR/aP1qLXPhtqOiaVdQzXV4hQ/PhQMcknFeXeAdRnSzR
 xG88UxGx9mVJPTnHGcGuk1+4h1LSnijRHkYlVjk4kVvQHHrQB+f8A4m/Zy8Y380kOnapokEZGS91c
 vk+wAQ1zq/svfEC5/cp4i0O2KHaR5UzsT/3yM19ea9aW8R3y3ElndKSWhdBtkB6HBxj6g1w+tarcx
 RRXLxS3mmxuE3ohCA9SNwwwP1oA8Ksv2WvHGAl143s40xjba6cznn/eYVFrX7LRuYUjbxa817nkTW
 B2MPqrEj9a9jXxZqt4DEkqoVb93DKN3b+8MEHjjI9Kq6j421mwSNL64ESSqytuYOj+gYdvqKAPAvF
 /wOv9EtR5z2tyEG3fDuX9GUYryPXfB15axs62ryxhtu+Ebxn6rmvpXW9cilF00+piYDO17cOVK9Mc
 9vqO1eQ+LNVSOd3g3NAvJMmUJ+vP9aAP/9k=
objectClass: inetOrgPerson
objectClass: top
sn:: 0JvQvtCz0L7Qv9C10LQ=

# search result
search: 2
result: 0 Success

# numResponses: 26
# numEntries: 25
