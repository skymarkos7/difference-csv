<p align="center"><a href="https://skymarkos7.github.io/front-compare/" target="_blank"><img src="https://miro.medium.com/v2/resize:fit:968/1*MssG8kpxsLBEMjjgn2aFPw.png" width="600" alt="Compare Logo"></a></p>



## Tools that you need to run
- git
- docker
- composer php

## To run the project

1. Clone this repository ``git clone https://github.com/skymarkos7/difference-csv.git``
2. Within the project of laravel run ``composer install`` 
    - you will need the vendor folder.
3. Within the project of laravel run ``./vendor/bin/sail sail up -d`` 
    - Explain: To create container Sail of laravel 
    - You can too, run ``php artisan server`` but will be necessary change the routes in front to ``http://127.0.0.1:800/``
    - If you are using windows or wsl, It may be necessary to use ``bash ./vendor/laravel/sail/bin/sail up``
4. Clone this repository `https://github.com/skymarkos7/front-compare.git`    
5. Open the front project and can using. Can you do this opening the front-end index.hml file or on [github pages](https://skymarkos7.github.io/front-compare/).

<hr>
## Comparison methodologies  

- <b>Git:</b> It is possible to check files of any format using the computer's git as a tool and capturing only the data output. With a few more adjustments it would be possible to check the difference between files of any format.

- <b>Simple:</b> Just simple comparation line by line and return more important info.</b>.

- <b>LCS:</b> longest common subsequence is an Advance metodology to get difference between files</b>.
<hr>

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
