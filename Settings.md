# LaravelS Settings

- `listen_ip`: `string` The listening ip, like local address `127.0.0.1`(IPv4) `::1`(IPv6), all addresses `0.0.0.0`(IPv4) `::`(IPv6), default `127.0.0.1`.

- `listen_port`: `int` The listening port, need `root` permission if port less than `1024`, default `5200`.

- `enable_gzip`: `bool` Whether enable the gzip of response content when respond by LaravelS. The header about Content-Encoding will be added automatically if enable, default `true`.

- `server`: `string` Set HTTP header `Server` when respond by LaravelS, default `LaravelS`.

- `handle_static`: `bool` Whether handle the static resource by LaravelS(Require `Swoole >= 1.7.21`, Handle by Swoole if `Swoole >= 1.9.17`), default `false`, Suggest that Nginx handles the statics and LaravelS handles the dynamics. The default path of static resource is `base_path('public')`, you can modify `swoole.document_root` to change it.

- `swoole`: `array` refer [Swoole Configuration](https://www.swoole.co.uk/docs/modules/swoole-server/configuration)