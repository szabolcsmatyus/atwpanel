<?php

namespace Pterodactyl\Models;

/**
 * @property int $id
 * @property string $uuid
 * @property int $nest_id
 * @property string $author
 * @property string $name
 * @property string|null $description
 * @property string $docker_image
 * @property string|null $config_files
 * @property string|null $config_startup
 * @property string|null $config_logs
 * @property string|null $config_stop
 * @property int|null $config_from
 * @property string|null $startup
 * @property bool $script_is_privileged
 * @property string|null $script_install
 * @property string $script_entry
 * @property string $script_container
 * @property int|null $copy_script_from
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property string|null $copy_script_install
 * @property string $copy_script_entry
 * @property string $copy_script_container
 * @property string|null $inherit_config_files
 * @property string|null $inherit_config_startup
 * @property string|null $inherit_config_logs
 * @property string|null $inherit_config_stop
 *
 * @property \Pterodactyl\Models\Nest $nest
 * @property \Illuminate\Database\Eloquent\Collection|\Pterodactyl\Models\Server[] $servers
 * @property \Illuminate\Database\Eloquent\Collection|\Pterodactyl\Models\EggVariable[] $variables
 * @property \Pterodactyl\Models\Egg|null $scriptFrom
 * @property \Pterodactyl\Models\Egg|null $configFrom
 */
class Egg extends Model
{
    /**
     * The resource name for this model when it is transformed into an
     * API representation using fractal.
     */
    const RESOURCE_NAME = 'egg';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eggs';

    /**
     * Fields that are not mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'docker_image',
        'config_files',
        'config_startup',
        'config_logs',
        'config_stop',
        'config_from',
        'startup',
        'script_is_privileged',
        'script_install',
        'script_entry',
        'script_container',
        'copy_script_from',
    ];

    /**
     * Cast values to correct type.
     *
     * @var array
     */
    protected $casts = [
        'nest_id' => 'integer',
        'config_from' => 'integer',
        'script_is_privileged' => 'boolean',
        'copy_script_from' => 'integer',
    ];

    /**
     * @var array
     */
    public static $validationRules = [
        'nest_id' => 'required|bail|numeric|exists:nests,id',
        'uuid' => 'required|string|size:36',
        'name' => 'required|string|max:255',
        'description' => 'string|nullable',
        'author' => 'required|string|email',
        'docker_image' => 'required|string|max:255',
        'startup' => 'required|nullable|string',
        'config_from' => 'sometimes|bail|nullable|numeric|exists:eggs,id',
        'config_stop' => 'required_without:config_from|nullable|string|max:255',
        'config_startup' => 'required_without:config_from|nullable|json',
        'config_logs' => 'required_without:config_from|nullable|json',
        'config_files' => 'required_without:config_from|nullable|json',
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'config_stop' => null,
        'config_startup' => null,
        'config_logs' => null,
        'config_files' => null,
    ];

    /**
     * Returns the install script for the egg; if egg is copying from another
     * it will return the copied script.
     *
     * @return string
     */
    public function getCopyScriptInstallAttribute()
    {
        if (! is_null($this->script_install) || is_null($this->copy_script_from)) {
            return $this->script_install;
        }

        return $this->scriptFrom->script_install;
    }

    /**
     * Returns the entry command for the egg; if egg is copying from another
     * it will return the copied entry command.
     *
     * @return string
     */
    public function getCopyScriptEntryAttribute()
    {
        if (! is_null($this->script_entry) || is_null($this->copy_script_from)) {
            return $this->script_entry;
        }

        return $this->scriptFrom->script_entry;
    }

    /**
     * Returns the install container for the egg; if egg is copying from another
     * it will return the copied install container.
     *
     * @return string
     */
    public function getCopyScriptContainerAttribute()
    {
        if (! is_null($this->script_container) || is_null($this->copy_script_from)) {
            return $this->script_container;
        }

        return $this->scriptFrom->script_container;
    }

    /**
     * Return the file configuration for an egg.
     *
     * @return string
     */
    public function getInheritConfigFilesAttribute()
    {
        if (! is_null($this->config_files) || is_null($this->config_from)) {
            return $this->config_files;
        }

        return $this->configFrom->config_files;
    }

    /**
     * Return the startup configuration for an egg.
     *
     * @return string
     */
    public function getInheritConfigStartupAttribute()
    {
        if (! is_null($this->config_startup) || is_null($this->config_from)) {
            return $this->config_startup;
        }

        return $this->configFrom->config_startup;
    }

    /**
     * Return the log reading configuration for an egg.
     *
     * @return string
     */
    public function getInheritConfigLogsAttribute()
    {
        if (! is_null($this->config_logs) || is_null($this->config_from)) {
            return $this->config_logs;
        }

        return $this->configFrom->config_logs;
    }

    /**
     * Return the stop command configuration for an egg.
     *
     * @return string
     */
    public function getInheritConfigStopAttribute()
    {
        if (! is_null($this->config_stop) || is_null($this->config_from)) {
            return $this->config_stop;
        }

        return $this->configFrom->config_stop;
    }

    /**
     * Gets nest associated with an egg.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nest()
    {
        return $this->belongsTo(Nest::class);
    }

    /**
     * Gets all servers associated with this egg.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function servers()
    {
        return $this->hasMany(Server::class, 'egg_id');
    }

    /**
     * Gets all variables associated with this egg.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function variables()
    {
        return $this->hasMany(EggVariable::class, 'egg_id');
    }

    /**
     * Get the parent egg from which to copy scripts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function scriptFrom()
    {
        return $this->belongsTo(self::class, 'copy_script_from');
    }

    /**
     * Get the parent egg from which to copy configuration settings.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function configFrom()
    {
        return $this->belongsTo(self::class, 'config_from');
    }
}
