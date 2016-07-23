#!/bin/sh

echo $0
echo $1
echo $2
exit 0
cd /vagrant/webcursos/mod/emarking/lib/qrextractor
java -jar emarking.jar http://webcursos.localhost.com/ admin pepito.P0 /opt/moodledata/temp/emarking/elUjIBsfYwfTIuq/Demo01-4pagesDigitizedAnswers.pdf /opt/moodledata/temp/emarking/6
