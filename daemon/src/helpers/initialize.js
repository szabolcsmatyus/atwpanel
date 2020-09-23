'use strict';

/**
 * Pterodactyl - Daemon
 * Copyright (c) 2015 - 2020 Dane Everitt <dane@daneeveritt.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
const rfr = require('rfr');
const Async = require('async');
const Path = require('path');
const Util = require('util');
const Fs = require('fs-extra');
const _ = require('lodash');
const Klaw = require('klaw');

const Log = rfr('src/helpers/logger.js');
const LoadConfig = rfr('src/helpers/config.js');
const Server = rfr('src/controllers/server.js');

const Config = new LoadConfig();
const Servers = {};

class Initialize {
    /**
     * Initializes all servers on the system and loads them into memory for NodeJS.
     * @param  {Function} next [description]
     * @return {[type]}        [description]
     */
    init(next) {
        this.folders = [];
        Klaw('./config/servers/').on('data', data => {
            this.folders.push(data.path);
        }).on('end', () => {
            Async.each(this.folders, (file, callback) => {
                if (Path.extname(file) !== '.json') {
                    return callback();
                }

                Fs.readJson(file).then(json => {
                    if (_.isUndefined(json.uuid)) {
                        Log.warn(Util.format('Detected valid JSON, but server was missing a UUID in %s, skipping...', file));
                        return callback();
                    }

                    const checkPath = Path.join(Config.get('sftp.path', '/srv/daemon-data'), json.uuid);
                    Fs.stat(checkPath).then(stats => {
                        if (!stats.isDirectory()) {
                            Log.warn({ server: json.uuid }, 'Detected that the server data directory is not a directory.');
                        }

                        this.setup(json, callback);
                    }).catch(err => {
                        if (err.code === 'ENOENT') {
                            Log.warn({ err, server: json.uuid }, 'Could not locate a server data directory. Skipping initialization of server.');
                            return callback();
                        }

                        return callback(err);
                    });
                }).catch(callback);
            }, next);
        });
    }

    /**
     * Performs the setup action for a specific server.
     * @param  {[type]}   json [description]
     * @param  {Function} next [description]
     * @return {[type]}        [description]
     */
    setup(json, next) {
        Async.series([
            callback => {
                if (!_.isUndefined(Servers[json.uuid])) {
                    delete Servers[json.uuid];
                }

                Servers[json.uuid] = new Server(json, callback);
            },
        ], err => {
            if (err) return next(err);

            Log.debug({ server: json.uuid }, 'Loaded configuration and initalized server.');
            return next(null, Servers[json.uuid]);
        });
    }

    /**
     * Sets up a server given its UUID.
     */
    setupByUuid(uuid, next) {
        Fs.readJson(Util.format('./config/servers/%s/server.json', uuid), (err, object) => {
            if (err) return next(err);
            this.setup(object, next);
        });
    }
}

exports.Initialize = Initialize;
exports.Servers = Servers;
