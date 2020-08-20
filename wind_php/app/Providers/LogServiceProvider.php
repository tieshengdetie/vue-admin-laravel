<?php
/**
 * Created by PhpStorm.
 * User: admin<admin@yixia.com>
 * Date: 2018/9/29
 * Time: 上午10:10
 */


namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Log\Writer;
use Monolog\Logger as Monolog;

/**
 * 日志记录器服务
 *
 * @package app.Providers
 */
class LogServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('log', function () {
            return $this->createAppLogger();
        });
        $this->app->singleton('sql-log', function () {
            return $this->createSqlLogger();
        });
        $this->app->singleton('api-log', function () {
            return $this->createApiLogger();
        });
    }

    /**
     * Create the app logger.
     *
     * @return \Illuminate\Log\Writer
     */
    public function createAppLogger()
    {
        $log = new Writer(
            new Monolog($this->channel()), $this->app['events']
        );
        $this->configureHandler($log, 'app');
        return $log;
    }

    /**
     * Create the sql logger.
     *
     * @return \Illuminate\Log\Writer
     */
    public function createSqlLogger()
    {
        $log = new Writer(
            new Monolog($this->channel()), $this->app['events']
        );
        $this->configureHandler($log, 'sql');
        return $log;
    }
    /*
     * create api logger
     *
     * @return \Illuminate\Log\Writer
     */
    public function createApiLogger()
    {
        $log = new Writer(
            new Monolog($this->channel()), $this->app['events']
        );
        $this->configureHandler($log, 'api');
        return $log;
    }
    /**
     * Get the name of the log "channel".
     *
     * @return string
     */
    protected function channel()
    {
        return $this->app->bound('env') ? $this->app->environment() : 'production';
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer $log
     * @param string $base_name
     * @return void
     */
    protected function configureHandler(Writer $log, $base_name)
    {
        $this->{'configure' . ucfirst($this->handler()) . 'Handler'}($log, $base_name);
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer $log
     * @param string $base_name
     * @return void
     */
    protected function configureSingleHandler(Writer $log, $base_name)
    {
        $log->useFiles(
            sprintf('%s/logs/%s%s.log', $this->app->storagePath(), $this->getFilePrefix(), $base_name),
            $this->logLevel()
        );
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer $log
     * @param string $base_name
     * @return void
     */
    protected function configureDailyHandler(Writer $log, $base_name)
    {
        $log->useDailyFiles(
            sprintf('%s/logs/%s%s.log.%s', $this->app->storagePath(), $this->getFilePrefix(), $base_name, Carbon::now()->format('Ymd')),
            $this->maxFiles(),
            $this->logLevel()
        );
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer $log
     * @param string $base_name
     * @return void
     */
    protected function configureSyslogHandler(Writer $log, $base_name)
    {
        $log->useSyslog($base_name, $this->logLevel());
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer $log
     * @param string $base_name
     * @return void
     */
    protected function configureErrorlogHandler(Writer $log, $base_name)
    {
        $log->useErrorLog($this->logLevel());
    }

    /**
     * Get the default log handler.
     *
     * @return string
     */
    protected function handler()
    {
        if ($this->app->bound('config')) {
            return $this->app->make('config')->get('app.log', 'single');
        }

        return 'single';
    }

    /**
     * Get the log level for the application.
     *
     * @return string
     */
    protected function logLevel()
    {
        if ($this->app->bound('config')) {
            return $this->app->make('config')->get('app.log_level', 'debug');
        }

        return 'debug';
    }

    /**
     * Get the maximum number of log files for the application.
     *
     * @return int
     */
    protected function maxFiles()
    {
        if ($this->app->bound('config')) {
            return $this->app->make('config')->get('app.log_max_files', 5);
        }

        return 0;
    }

    /**
     * 获取文件前缀
     *
     * @return string
     */
    private function getFilePrefix()
    {
        return php_sapi_name() == 'cli' ? 'cli_' : '';
    }
}
